<?php

namespace App\Http\Controllers\Api\V1\Faculty;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Faculty\CourseDetailsWizardRequest;
use App\Models\Committee;
use App\Models\CourseOutcome;
use App\Models\CourseOutcomeABCD;
use App\Models\CourseOutcomeCPA;
use App\Models\TLAMethod;
use App\Models\TLATask;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CourseDetailsWizardController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Faculty_Member');
    }

    public function submit(CourseDetailsWizardRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

            $curriculumCourseId = $validated['curriculum_course_id'];

            foreach ($validated['course_outcomes'] as $coData) {
                // 1. Create Course Outcome
                $courseOutcome = CourseOutcome::create([
                    'curriculum_course_id' => $curriculumCourseId,
                    'name' => $coData['name'],
                    'statement' => $coData['statement'],
                ]);


                // 2. Create ABCD Mapping
                CourseOutcomeABCD::create([
                    'co_id' => $courseOutcome->id,
                    'audience' => $coData['abcd']['audience'],
                    'behavior' => $coData['abcd']['behavior'],
                    'condition' => $coData['abcd']['condition'],
                    'degree' => $coData['abcd']['degree'],
                ]);

                // 3. Create CPA Domain

                CourseOutcomeCPA::create([
                    'co_id' => $courseOutcome->id,
                    'cpa' => $coData['cpa'],
                ]);
                // 4. Map to Program Outcomes
                $poMappings  = [];

                foreach ($coData['po_mappings'] as $mapping) {
                    $poMappings[$mapping['po_id']] = ['ied' => $mapping['ied']];
                }
                $courseOutcome->pos()->attach($poMappings);

                // 5. Create tla tasks
                foreach ($coData['tla_tasks'] as $taskData) {
                    TLATask::create([
                        'co_id' => $courseOutcome->id,
                        'at_code' => $taskData['at_code'],
                        'at_name' => $taskData['at_name'],
                        'at_tool' => $taskData['at_tool'],
                        'weight' => $taskData['at_weight'],
                    ]);
                }

                // 6. Create TLA assessment method
                TLAMethod::create([
                    'co_id' => $courseOutcome->id,
                    'teaching_methods' => $coData['tla_assessment_method']['teaching_methods'],
                    'learning_resources' => $coData['tla_assessment_method']['learning_resources'],
                ]);

                $committee = Committee::where('user_id', auth()->id())
                    ->whereHas('curriculumCourses', function ($q) use ($curriculumCourseId) {
                        $q->where('curriculum_course_id', $curriculumCourseId);
                    })->first();

                if ($committee) {
                    $committee->curriculumCourses()->updateExistingPivot($curriculumCourseId, ['is_completed' => true]);
                } else {
                    // Log or handle the missing committee case
                    DB::rollBack();
                    return response()->json([
                        'message' => 'You are not authorized to mark this course as complete',
                        'error' => 'No committee assignment found for this user and curriculum course'
                    ], 403);
                }

                DB::commit();

                return response()->json([
                    'message' => 'course outcome details created successfully '
                ], 200);
            }
        } catch (Exception $e) {
            //throw $th;
            return  response()->json([
                'message' => 'failed to create curriculum course details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
