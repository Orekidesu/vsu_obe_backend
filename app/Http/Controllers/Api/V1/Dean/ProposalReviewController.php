<?php

namespace App\Http\Controllers\Api\V1\Dean;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Dean\ProposalReviewRequest;
use App\Models\ProgramProposal;
use App\Models\ProgramProposalRevision;
use App\Models\Program;
use Illuminate\Support\Facades\DB;
use Exception;


class ProposalReviewController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Dean');
    }

    //
    // public function review(ProposalReviewRequest $request, ProgramProposal $programProposal)
    // {
    //     DB::beginTransaction();
    //     try {
    //         $data = $request->validated();
    //         if ($programProposal->status !== 'pending') {
    //             return response()->json([
    //                 'message' => 'This proposal has already been reviewed.',
    //             ], 409);
    //         }

    //         if ($data['status'] === 'approved') {
    //             Program::where('id', $programProposal->program_id)
    //                 ->where('status', 'active')
    //                 ->update(['status' => 'archived']);

    //             $programProposal->program()->update(['status' => 'active']);
    //             $programProposal->update(['status' => 'approved']);

    //             DB::commit();

    //             return response()->json([
    //                 'message' => 'Program proposal approved successfully.',
    //             ], 200);
    //         }
    //         if ($data['status'] === 'revision') {
    //             // 1. Update the program proposal status to "revision"
    //             $programProposal->update([
    //                 'status' => 'revision'
    //             ]);

    //             //  2. Loop through the Department Level Issues
    //             foreach ($data['department_level'] as $departmentIssue) {
    //                 ProgramProposalRevision::create([
    //                     'program_proposal_id' => $programProposal->id,
    //                     'level' => 'department',
    //                     'section' => $departmentIssue['section'],
    //                     'details' => $departmentIssue['details']
    //                 ]);
    //             }

    //             //  3. Loop through the Committee Level Issues
    //             foreach ($data['committee_level'] as $committeeIssue) {
    //                 ProgramProposalRevision::create([
    //                     'program_proposal_id' => $programProposal->id,
    //                     'level' => 'committee',
    //                     'section' => 'course_outcomes', // fixed value
    //                     'curriculum_course_id' => $committeeIssue['curriculum_course_id'],
    //                     'details' => $committeeIssue['details']
    //                 ]);

    //                 //  4. Mark the curriculum course as incomplete
    //                 $committees = $programProposal->committees;
    //                 foreach ($committees as $committee) {
    //                     // Update the pivot entries for this curriculum course
    //                     $committee->curriculumCourses()->updateExistingPivot(
    //                         $committeeIssue['curriculum_course_id'],
    //                         ['is_completed' => false]
    //                     );
    //                 }
    //             }
    //         }
    //         DB::commit();
    //         return response()->json([
    //             'message' => $data['status'] === 'approved' ?
    //                 'Program proposal approved successfully.' :
    //                 'Revision request submitted successfully.'
    //         ], 200);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json([
    //             'message' => 'Failed to review proposal',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function review(ProposalReviewRequest $request, ProgramProposal $programProposal)
    {
        $data = $request->validated();

        //  Check if the proposal is still pending
        if ($programProposal->status !== 'pending') {
            return response()->json([
                'message' => 'This proposal has already been reviewed.',
            ], 409);
        }

        try {
            //  Begin the transaction
            DB::transaction(function () use ($data, $programProposal) {
                //  Approval Process
                if ($data['status'] === 'approved') {
                    // Archive any existing active version of the program
                    Program::where('id', $programProposal->program_id)
                        ->where('status', 'active')
                        ->update(['status' => 'archived']);

                    // Activate the new program version
                    $programProposal->program()->update(['status' => 'active']);
                    $programProposal->update(['status' => 'approved']);
                }

                //  Revision Process
                if ($data['status'] === 'revision') {
                    // Mark the proposal as requiring revision
                    $programProposal->update(['status' => 'revision']);

                    //  Loop through Department Level Issues
                    foreach ($data['department_level'] as $departmentIssue) {
                        ProgramProposalRevision::create([
                            'program_proposal_id' => $programProposal->id,
                            'level' => 'department',
                            'section' => $departmentIssue['section'],
                            'details' => $departmentIssue['details']
                        ]);
                    }

                    //  Loop through Committee Level Issues
                    foreach ($data['committee_level'] as $committeeIssue) {
                        ProgramProposalRevision::create([
                            'program_proposal_id' => $programProposal->id,
                            'level' => 'committee',
                            'section' => 'course_outcomes',
                            'curriculum_course_id' => $committeeIssue['curriculum_course_id'],
                            'details' => $committeeIssue['details']
                        ]);

                        // Mark the curriculum course as not completed
                        $committees = $programProposal->committees;
                        foreach ($committees as $committee) {
                            $updated = $committee->curriculumCourses()
                                ->updateExistingPivot(
                                    $committeeIssue['curriculum_course_id'],
                                    ['is_completed' => false]
                                );

                            // Check if update was successful, log if not
                            if (!$updated) {
                                logger()->warning("Failed to update is_completed for curriculum_course_id: {$committeeIssue['curriculum_course_id']}");
                            }
                        }
                    }
                }
            });

            //  Return a success response based on action
            return response()->json([
                'message' => $data['status'] === 'approved' ?
                    'Program proposal approved successfully.' :
                    'Revision request submitted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to review program proposal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}