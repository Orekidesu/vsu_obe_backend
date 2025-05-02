<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramProposalWizardRequest;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Program;
use App\Models\ProgramProposal;
use App\Models\Semester;

class ProgramProposalWizardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }


    // Note: this controller is made with proposing a program in mind
    // not revising a proposed program
    public function submit(ProgramProposalWizardRequest $request)
    {

        DB::beginTransaction();
        $validated = $request->validated();


        try {
            /*1. Create Program or Get Existing program */

            $programData  = $validated['program'];

            // Check existing program proposal
            $existingPending = Program::where('name', $programData['name'])
                ->where('abbreviation', $programData['abbreviation'])
                ->where('status', 'pending')
                ->exists();

            if ($existingPending) {
                DB::rollBack(); // Close the transaction before returning
                return response()->json([
                    'message' => 'A pending version of program already exists',
                ], 409);
            }

            // Determine Program Version
            $latestVersion =  Program::where('name', $programData['name'])
                ->where('abbreviation', $programData['abbreviation'])
                ->max('version');
            $newVersion = $latestVersion ? $latestVersion + 1 : 1;

            // Create Program
            $program = Program::create([
                'name' => $programData['name'],
                'abbreviation' => $programData['abbreviation'],
                'department_id' => auth()->user()->department_id,
                'version' => $newVersion,
                'status' => 'pending',
            ]);

            // Create Proposal
            $proposal = ProgramProposal::create([
                'program_id' => $program->id,
                'abbreviation' => $program->abbreviation,
                'version' => $program->version,
                'status' => 'pending',
                'comment' =>  null,
            ]);

            /*2. PEOs */
            $peos = $validated['peos'];
            $peoIndexMap = [];

            foreach ($peos as $index => $peoData) {
                // $peo = $program->programEducationalObjectives()->create(['statement' => $peoData['statement']]);
                $peo = $proposal->peos()->create(['statement' => $peoData['statement']]);
                $peoIndexMap[$index] = $peo->id;
            }

            /*3. PEO to Mission */
            $peoMissionMappings = $validated['peo_mission_mappings'];
            foreach ($peoMissionMappings as $map) {

                $peoId = $peoIndexMap[$map['peo_index']];

                DB::table('program_educational_objective_mission')->insert([
                    'peo_id' => $peoId,
                    'mission_id' => $map['mission_id'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            /*4. GA to PEO */
            $gaPeoMappings = $validated['ga_peo_mappings'];

            foreach ($gaPeoMappings as $map) {
                $peoId = $peoIndexMap[$map['peo_index']];

                DB::table('graduate_attribute_peo')->insert([
                    'ga_id' => $map['ga_id'],
                    'peo_id' => $peoId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            /*5. Program Outcomes */
            $pos = $validated['pos'];
            $poIndexMap = [];

            foreach ($pos as $index => $poData) {
                // $po = $program->programOutcomes()->create([
                //     'name' => $poData['name'],
                //     'statement' => $poData['statement'],
                // ]);
                $po = $proposal->pos()->create([
                    'name' => $poData['name'],
                    'statement' => $poData['statement'],
                ]);
                $poIndexMap[$index] = $po->id;
            }

            /*6. PO To PEO */
            $poPeoMappings = $validated['po_peo_mappings'];
            foreach ($poPeoMappings as $map) {
                $poId = $poIndexMap[$map['po_index']];
                $peoId = $peoIndexMap[$map['peo_index']];

                DB::table('program_outcome_peo')->insert([
                    'po_id' => $poId,
                    'peo_id' => $peoId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }


            /*7. PO to GA */
            $poGaMappings = $validated['po_ga_mappings'];
            foreach ($poGaMappings as $map) {
                $poId = $poIndexMap[$map['po_index']];

                DB::table('program_outcome_ga')->insert([
                    'po_id' => $poId,
                    'ga_id' => $map['ga_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            /*8. Curriculum */
            $curriculumData = $validated['curriculum'];
            $curriculum = $proposal->curriculum()->create([
                'name' => $curriculumData['name'],
            ]);
            /*9. Semester */
            $semesters = $validated['semesters'];
            $semesterMap = [];

            foreach ($semesters as $semesterData) {
                // use first or create for unique
                $semester = Semester::firstOrCreate([
                    'year' => $semesterData['year'],
                    'sem' => $semesterData['sem'],
                ]);
                // will be use later for curriculum_course
                $key = $semesterData['year'] . '-' . $semesterData['sem'];
                $semesterMap[$key] = $semester->id;
            }


            /*10. Course Category */
            $courseCategories = $validated['course_categories'];
            $categoryMap = [];

            foreach ($courseCategories as $categoryData) {
                // Check if category already exists for this curriculum
                $existingCategory = DB::table('course_categories')
                    ->where('name', $categoryData['name'])
                    ->where('code', $categoryData['code'])
                    ->first();

                if ($existingCategory) {
                    $category = $existingCategory;
                    $categoryId = $existingCategory->id;
                } else {
                    $category = DB::table('course_categories')->insert([
                        'name' => $categoryData['name'],
                        'code' => $categoryData['code'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $categoryId = DB::getPdo()->lastInsertId();
                }

                $categoryMap[$categoryData['code']] = $categoryId;
            }

            /*11. Courses and Curriculum Courses */

            $courses = $validated['courses'];
            $curriculumCourses = $validated['curriculum_courses'];
            $courseMap = [];
            // $departmentId = auth()->user()->department_id;

            // First create or update courses
            foreach ($courses as $courseData) {
                // check if the course exist

                $existingCourse = DB::table('courses')->where('code', $courseData['code'])
                    ->where('descriptive_title', $courseData['descriptive_title'])->first();

                if ($existingCourse) {
                    $courseId = $existingCourse->id;
                } else {
                    // Create new course
                    DB::table('courses')->insert([
                        'code' => $courseData['code'],
                        'descriptive_title' => $courseData['descriptive_title'],
                        // 'department_id' => $departmentId,
                        'created_at' => now(),
                        'updated_at' => now()

                    ]);
                    $courseId = DB::getPdo()->lastInsertId();
                }
                $courseMap[$courseData['code']] = $courseId;
            }

            // Create Curriculum Courses
            foreach ($curriculumCourses as $ccData) {
                $semesterKey = $ccData['semester_year'] . '-' . $ccData['semester_name'];

                DB::table('curriculum_courses')->insert([
                    'curriculum_id' => $curriculum->id,
                    'course_id' => $courseMap[$ccData['course_code']],
                    'course_category_id' => $categoryMap[$ccData['category_code']],
                    'semester_id' => $semesterMap[$semesterKey],
                    'unit' => $ccData['units'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            /*12. Curriculum Course to PO with IED */
            $coursePOMappings = $validated['course_po_mappings'];
            foreach ($coursePOMappings as $mapping) {
                $courseId = $courseMap[$mapping['course_code']];
                $poCode = $mapping['po_code'];

                // find the PO ID from the name/code
                $poId = null;

                // find po id
                foreach ($poIndexMap as $index => $id) {
                    if ($pos[$index]['name'] === $poCode) {
                        $poId = $id;
                        break;
                    }
                }

                if (!$poId) {
                    continue; // skip if poid is not found
                }

                $curriculumCourseId = DB::table('curriculum_courses')
                    ->where('curriculum_id', $curriculum->id)
                    ->where('course_id', $courseId)
                    ->value('id');
                if (!$curriculumCourseId) {
                    continue; // skip if curriculum course is not found
                }



                // Better implementation (creates one row with all IED values)
                DB::table('curriculum_course_po')->insert([
                    'curriculum_course_id' => $curriculumCourseId,
                    'po_id' => $poId,
                    'ied' => json_encode($mapping['ied']),  // Stores the entire array like ["I", "R"]
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            /*13. Committees */
            $committees = $validated['committees'];
            $committeeMap = []; // Map user_id to committee_id

            foreach ($committees as $committeeData) {
                $committee = DB::table('committees')->insert([
                    'program_proposal_id' => $proposal->id,
                    'user_id' => $committeeData['user_id'],
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $committeeId = DB::getPdo()->lastInsertId();
                $committeeMap[$committeeData['user_id']] = $committeeId;
            }

            /*14. Committee Course Assignments */
            $committeeCourseAssignments = $validated['committee_course_assignments'];

            foreach ($committeeCourseAssignments as $assignment) {
                $userId = $assignment['user_id'];
                $committeeId = $committeeMap[$userId];

                foreach ($assignment['course_codes'] as $courseCode) {
                    $courseId = $courseMap[$courseCode];

                    // Find curriculum course ID
                    $curriculumCourseId = DB::table('curriculum_courses')
                        ->where('curriculum_id', $curriculum->id)
                        ->where('course_id', $courseId)
                        ->value('id');

                    if (!$curriculumCourseId) {
                        continue; // Skip if curriculum course not found
                    }

                    // Create the assignment
                    DB::table('committee_course_assignments')->insert([
                        'committee_id' => $committeeId,
                        'curriculum_course_id' => $curriculumCourseId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'message' => 'proposal submitted successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'messsage' => 'Failed to submit proposal',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}