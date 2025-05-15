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


    public function review(ProposalReviewRequest $request, ProgramProposal $programProposal)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            if ($programProposal->status !== 'review') {
                return response()->json([
                    'message' => 'This proposal has already been reviewed.',
                ], 409);
            }

            //  Approval Process
            if ($data['status'] === 'approved') {
                // Get the program details to find others with the same name/abbreviation
                $program = $programProposal->program;

                // Archive any existing active version of the program with the same name and abbreviation
                Program::where('name', $program->name)
                    ->where('abbreviation', $program->abbreviation)
                    ->where('status', 'active')
                    ->where('id', '!=', $programProposal->program_id) // Exclude the current program
                    ->update(['status' => 'archived']);

                // Activate the new program version
                $programProposal->program()->update(['status' => 'active']);
                $programProposal->update(['status' => 'approved']);
            }
            if ($data['status'] === 'revision') {
                // 1. Update the program proposal status to "revision"
                $programProposal->update([
                    'status' => 'revision'
                ]);

                //  2. Loop through the Department Level Issues
                if (!empty($data['department_level'])) {

                    $programProposal->update(['department_revision_required' => true]);

                    foreach ($data['department_level'] as $departmentIssue) {
                        ProgramProposalRevision::create([
                            'program_proposal_id' => $programProposal->id,
                            'level' => 'department',
                            'section' => $departmentIssue['section'],
                            'details' => $departmentIssue['details']
                        ]);
                    }
                }

                //  3. Loop through the Committee Level Issues
                if (!empty($data['committee_level'])) {
                    $programProposal->update(['committee_revision_required' => true]);
                    foreach ($data['committee_level'] as $committeeIssue) {
                        ProgramProposalRevision::create([
                            'program_proposal_id' => $programProposal->id,
                            'level' => 'committee',
                            'curriculum_course_id' => $committeeIssue['curriculum_course_id'],
                            'section' => $committeeIssue['section'],
                            'details' => $committeeIssue['details']
                        ]);

                        //  4. Mark the curriculum course as incomplete
                        $committees = $programProposal->committees;
                        foreach ($committees as $committee) {
                            // Update the pivot entries for this curriculum course
                            $committee->curriculumCourses()->updateExistingPivot(
                                $committeeIssue['curriculum_course_id'],
                                ['is_completed' => false]
                            );
                        }
                    }
                }
            }
            DB::commit();
            return response()->json([
                'message' => $data['status'] === 'approved' ?
                    'Program proposal approved successfully.' :
                    'Revision request submitted successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to review proposal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
