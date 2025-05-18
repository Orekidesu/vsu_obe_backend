<?php

namespace App\Http\Controllers\Api\V1\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Faculty\CommitteeRevisionRequest;
use App\Models\CurriculumCourse;
use App\Models\CourseOutcome;
use App\Models\TLATask;
use App\Models\CourseOutcomeABCD;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Committee;

class CommitteeRevisionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Faculty_Member');
    }



    public function handleCommitteeLevelRevision(CommitteeRevisionRequest $request, CurriculumCourse $curriculumCourse)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $courseOutcomeMap = [];

            $committee = Committee::where('user_id', auth()->id())
                ->whereHas('curriculumCourses', function ($q) use ($curriculumCourse) {
                    $q->where('curriculum_course_id', $curriculumCourse->id);
                })->first();

            /**
             * Full Sync for Course Outcomes 
             * Delete course outcomes that exist in DB but aren't in the request
             */
            $existingCOIds = CourseOutcome::where('curriculum_course_id', $curriculumCourse->id)
                ->pluck('id')->toArray();

            $requestCOIds = collect($data['course_outcomes'])
                ->pluck('id')
                ->filter() // Remove null values for new COs
                ->toArray();

            CourseOutcome::where('curriculum_course_id', $curriculumCourse->id)
                ->whereNotIn('id', $requestCOIds)
                ->delete();

            /**
             * 1️. Loop through Course Outcomes
             */
            foreach ($data['course_outcomes'] as $outcomeData) {
                // If ID exists, update, otherwise create
                if (!empty($outcomeData['id'])) {
                    $courseOutcome = CourseOutcome::updateOrCreate(
                        ['id' => $outcomeData['id']],
                        [
                            'curriculum_course_id' => $curriculumCourse->id,
                            'name' => $outcomeData['name'],
                            'statement' => $outcomeData['statement'],

                        ]
                    );
                } else {
                    $courseOutcome = CourseOutcome::create([
                        'curriculum_course_id' => $curriculumCourse->id,
                        'name' => $outcomeData['name'],
                        'statement' => $outcomeData['statement'],
                        'cpa' => $outcomeData['cpa'] ?? 'C' // Default to C if missing
                    ]);
                }

                $courseOutcomeMap[$outcomeData['name']] = $courseOutcome->id;

                /**
                 * 2️. Update ABCD Model (conditional)
                 */
                if (isset($outcomeData['abcd'])) {
                    CourseOutcomeAbcd::updateOrCreate(
                        ['co_id' => $courseOutcome->id],
                        [
                            'audience' => $outcomeData['abcd']['audience'],
                            'behavior' => $outcomeData['abcd']['behavior'],
                            'condition' => $outcomeData['abcd']['condition'],
                            'degree' => $outcomeData['abcd']['degree']
                        ]
                    );
                }

                /**
                 * 3️. Update CO-PO Mappings (Full Sync, conditional)
                 */
                if (isset($outcomeData['po_mappings'])) {
                    $poIds = collect($outcomeData['po_mappings'])->pluck('po_id')->toArray();
                    DB::table('course_outcome_po')
                        ->where('co_id', $courseOutcome->id)
                        ->whereNotIn('po_id', $poIds)
                        ->delete();

                    foreach ($outcomeData['po_mappings'] as $mapping) {
                        DB::table('course_outcome_po')->updateOrInsert(
                            [
                                'co_id' => $courseOutcome->id,
                                'po_id' => $mapping['po_id']
                            ],
                            [
                                'ied' => $mapping['ied'],
                                'created_at' => now(),
                                'updated_at' => now()
                            ]
                        );
                    }
                }

                /**
                 * 4️. Update TLA Tasks (Full Sync, conditional)
                 */
                if (isset($outcomeData['tla_tasks'])) {
                    $tlaTaskIds = collect($outcomeData['tla_tasks'])->pluck('id')->filter()->toArray();
                    TlaTask::where('co_id', $courseOutcome->id)
                        ->whereNotIn('id', $tlaTaskIds)
                        ->delete();

                    foreach ($outcomeData['tla_tasks'] as $taskData) {
                        TlaTask::updateOrCreate(
                            ['id' => $taskData['id'] ?? null],
                            [
                                'co_id' => $courseOutcome->id,
                                'at_code' => $taskData['at_code'],
                                'at_name' => $taskData['at_name'],
                                'at_tool' => $taskData['at_tool'],
                                'weight' => $taskData['weight'] ?? $taskData['at_weight'] ?? 0 // Flexible field name
                            ]
                        );
                    }
                }

                /**
                 * 5️. Update TLA Assessment Method (conditional)
                 */
                if (isset($outcomeData['tla_assessment_method'])) {
                    $courseOutcome->tlaAssessmentMethod()->updateOrCreate(
                        ['co_id' => $courseOutcome->id],
                        [
                            'teaching_methods' => json_encode($outcomeData['tla_assessment_method']['teaching_methods'] ?? []),
                            'learning_resources' => json_encode($outcomeData['tla_assessment_method']['learning_resources'] ?? []),
                        ]
                    );
                }
            }

            // Mark the Curriculum Course as completed
            if ($committee) {
                // Mark the Curriculum Course as completed
                $committee->curriculumCourses()->updateExistingPivot(
                    $curriculumCourse,
                    ['is_completed' => true]
                );
            } else {
                return response()->json([
                    'message' => 'attempted to update curriculum course but has no committee assignment'
                ], 500);
            }

            DB::commit();

            return response()->json(['message' => 'Committee-level revision handled successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process committee-level revision.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
