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
                $peo = $program->programEducationalObjective()->create(['statement' => $peoData['statement']]);
                $peoIndexMap[$index] = $peo->id;
            }

            /*3. PEO to Mission */
            $peoMissionMappings = $validated['peo_mission_mappings'];
            foreach ($peoMissionMappings as $map) {

                $peoId = $peoIndexMap[$map['peo_index']];

                DB::table('program_educational_objective_mission')->insert([
                    'peo_id' => $peoId,
                    'mission_id' => $map['mission_id'],
                ]);
            }
            /*4. GA to PEO */
            $gaPeoMappings = $validated['ga_peo_mappings'];

            foreach ($gaPeoMappings as $map) {
                $peoId = $peoIndexMap[$map['peo_index']];

                DB::table('graduate_attribute_peo')->insert([
                    'ga_id' => $map['ga_id'],
                    'peo_id' => $peoId,
                ]);
            }

            /*5. Program Outcomes */
            $pos = $validated['pos'];
            $poIndexMap = [];

            foreach ($pos as $index => $poData) {
                $po = $program->programOutcomes()->create([
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
                ]);
            }


            /*7. PO to GA */
            $poGaMappings = $validated['po_ga_mappings'];
            foreach ($poGaMappings as $map) {
                $poId = $poIndexMap[$map['po_index']];

                DB::table('program_outcome_ga')->insert([
                    'po_id' => $poId,
                    'ga_id' => $map['ga_id'],
                ]);
            }
            /*8. Curriculum */
            $curriculumData = $validated['curriculum'];
            $curriculum = $program->curriculum()->create([
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
                $category = $curriculum->courseCategories()->create([
                    'name' => $categoryData['name'],
                    'code' => $categoryData['code'],
                ]);

                $categoryMap[$categoryData['code']] = $category->id;
            }

            /*11. Courses and Curriculum Courses */

            $courses = $validated['courses'];
            $curriculumCourses = $validated['curriculum_courses'];
            $courseMap = [];
            $departmentId = auth()->user()->department_id;

            // First create or update courses
            foreach ($courses as $courseData) {
                // check if the course exist

                $existingCourse = DB::table('courses')->where('code', $courseData['code'])->first();

                if ($existingCourse) {
                    $courseId = $existingCourse->id;
                } else {
                    // Create new course
                    DB::table('courses')->insert([
                        'code' => $courseData['code'],
                        'descriptive_title' => $courseData['descriptive_title'],
                        'department_id' => $departmentId,
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

            /*12. Curriculum Course to PO with IRD */

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

                // insert each IRD value
                foreach ($mapping['ird'] as $ird) {
                    DB::table('curriculum_course_po')->insert([
                        'curriculum_course_id' => $curriculumCourseId,
                        'po_id' => $poId,
                        'ird' => json_encode($ird),
                        'created_at' => now(),
                        'updated_at' => now()
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



    // with logs

    // public function submit(ProgramProposalWizardRequest $request)
    // {
    //     DB::beginTransaction();
    //     $validated = $request->validated();
    //     Log::debug('Starting program proposal wizard submission', ['data' => $validated]);

    //     try {
    //         /*1. Create Program or Get Existing program */
    //         Log::debug('Step 1: Creating/Checking Program');
    //         $programData = $validated['program'];

    //         // Check existing program proposal
    //         $existingPending = Program::where('name', $programData['name'])
    //             ->where('abbreviation', $programData['abbreviation'])
    //             ->where('status', 'pending')
    //             ->exists();

    //         if ($existingPending) {
    //             Log::debug('Found existing pending program, aborting', [
    //                 'name' => $programData['name'],
    //                 'abbreviation' => $programData['abbreviation']
    //             ]);
    //             DB::rollBack(); // Close the transaction before returning
    //             return response()->json([
    //                 'message' => 'A pending version of program already exists',
    //             ], 409);
    //         }

    //         // Determine Program Version
    //         $latestVersion = Program::where('name', $programData['name'])
    //             ->where('abbreviation', $programData['abbreviation'])
    //             ->max('version');
    //         $newVersion = $latestVersion ? $latestVersion + 1 : 1;
    //         Log::debug('Program version determined', ['version' => $newVersion]);

    //         // Create Program
    //         $program = Program::create([
    //             'name' => $programData['name'],
    //             'abbreviation' => $programData['abbreviation'],
    //             'department_id' => auth()->user()->department_id,
    //             'version' => $newVersion,
    //             'status' => 'pending',
    //         ]);
    //         Log::debug('Program created', ['program_id' => $program->id]);

    //         // Create Proposal
    //         $proposal = ProgramProposal::create([
    //             'program_id' => $program->id,
    //             'abbreviation' => $program->abbreviation,
    //             'version' => $program->version,
    //             'status' => 'pending',
    //             'comment' => null,
    //         ]);
    //         Log::debug('Program proposal created', ['proposal_id' => $proposal->id]);

    //         /*2. PEOs */
    //         Log::debug('Step 2: Creating PEOs');
    //         $peos = $validated['peos'];
    //         $peoIndexMap = [];

    //         foreach ($peos as $index => $peoData) {
    //             $peo = $program->programEducationalObjective()->create(['statement' => $peoData['statement']]);
    //             $peoIndexMap[$index] = $peo->id;
    //             Log::debug('PEO created', ['index' => $index, 'peo_id' => $peo->id, 'statement' => $peoData['statement']]);
    //         }

    //         /*3. PEO to Mission */
    //         Log::debug('Step 3: Mapping PEOs to Missions');
    //         $peoMissionMappings = $validated['peo_mission_mappings'];
    //         foreach ($peoMissionMappings as $map) {
    //             $peoId = $peoIndexMap[$map['peo_index']];
    //             Log::debug('Processing PEO-Mission mapping', [
    //                 'peo_index' => $map['peo_index'],
    //                 'peo_id' => $peoId,
    //                 'mission_id' => $map['mission_id']
    //             ]);

    //             DB::table('program_educational_objective_mission')->insert([
    //                 'peo_id' => $peoId,
    //                 'mission_id' => $map['mission_id'],
    //             ]);
    //             Log::debug('PEO-Mission mapping created', ['peo_id' => $peoId, 'mission_id' => $map['mission_id']]);
    //         }

    //         /*4. GA to PEO */
    //         Log::debug('Step 4: Mapping GAs to PEOs');
    //         $gaPeoMappings = $validated['ga_peo_mappings'];

    //         foreach ($gaPeoMappings as $map) {
    //             $peoId = $peoIndexMap[$map['peo_index']];
    //             Log::debug('Processing GA-PEO mapping', [
    //                 'peo_index' => $map['peo_index'],
    //                 'peo_id' => $peoId,
    //                 'ga_id' => $map['ga_id']
    //             ]);

    //             DB::table('graduate_attribute_peo')->insert([
    //                 'ga_id' => $map['ga_id'],
    //                 'peo_id' => $peoId,
    //             ]);
    //             Log::debug('GA-PEO mapping created', ['ga_id' => $map['ga_id'], 'peo_id' => $peoId]);
    //         }

    //         /*5. Program Outcomes */
    //         Log::debug('Step 5: Creating Program Outcomes');
    //         $pos = $validated['pos'];
    //         $poIndexMap = [];

    //         foreach ($pos as $index => $poData) {
    //             Log::debug('Creating PO', ['index' => $index, 'data' => $poData]);
    //             $po = $program->programOutcomes()->create([
    //                 'name' => $poData['name'],
    //                 'statement' => $poData['statement'],
    //             ]);
    //             $poIndexMap[$index] = $po->id;
    //             Log::debug('PO created', ['index' => $index, 'po_id' => $po->id]);
    //         }

    //         /*6. PO To PEO */
    //         Log::debug('Step 6: Mapping POs to PEOs');
    //         $poPeoMappings = $validated['po_peo_mappings'];
    //         foreach ($poPeoMappings as $map) {
    //             $poId = $poIndexMap[$map['po_index']];
    //             $peoId = $peoIndexMap[$map['peo_index']];
    //             Log::debug('Processing PO-PEO mapping', [
    //                 'po_index' => $map['po_index'],
    //                 'peo_index' => $map['peo_index'],
    //                 'po_id' => $poId,
    //                 'peo_id' => $peoId
    //             ]);

    //             DB::table('program_outcome_peo')->insert([
    //                 'po_id' => $poId,
    //                 'peo_id' => $peoId,
    //             ]);
    //             Log::debug('PO-PEO mapping created', ['po_id' => $poId, 'peo_id' => $peoId]);
    //         }

    //         /*7. PO to GA */
    //         Log::debug('Step 7: Mapping POs to GAs');
    //         $poGaMappings = $validated['po_ga_mappings'];
    //         foreach ($poGaMappings as $map) {
    //             $poId = $poIndexMap[$map['po_index']];
    //             Log::debug('Processing PO-GA mapping', [
    //                 'po_index' => $map['po_index'],
    //                 'po_id' => $poId,
    //                 'ga_id' => $map['ga_id']
    //             ]);

    //             DB::table('program_outcome_ga')->insert([
    //                 'po_id' => $poId,
    //                 'ga_id' => $map['ga_id'],
    //             ]);
    //             Log::debug('PO-GA mapping created', ['po_id' => $poId, 'ga_id' => $map['ga_id']]);
    //         }

    //         /*8. Curriculum */
    //         Log::debug('Step 8: Creating Curriculum');
    //         $curriculumData = $validated['curriculum'];
    //         $curriculum = $program->curriculum()->create([
    //             'name' => $curriculumData['name'],
    //         ]);
    //         Log::debug('Curriculum created', ['curriculum_id' => $curriculum->id, 'name' => $curriculumData['name']]);

    //         /*9. Semester */
    //         Log::debug('Step 9: Processing Semesters');
    //         $semesters = $validated['semesters'];
    //         $semesterMap = [];

    //         foreach ($semesters as $semesterData) {
    //             Log::debug('Processing semester', $semesterData);
    //             // use first or create for unique
    //             $semester = Semester::firstOrCreate([
    //                 'year' => $semesterData['year'],
    //                 'sem' => $semesterData['sem'],
    //             ]);
    //             // will be use later for curriculum_course
    //             $key = $semesterData['year'] . '-' . $semesterData['sem'];
    //             $semesterMap[$key] = $semester->id;
    //             Log::debug('Semester processed', ['key' => $key, 'semester_id' => $semester->id]);
    //         }

    //         /*10. Course Category */
    //         Log::debug('Step 10: Creating Course Categories');
    //         $courseCategories = $validated['course_categories'];
    //         $categoryMap = [];

    //         foreach ($courseCategories as $categoryData) {
    //             Log::debug('Processing category', $categoryData);
    //             $category = $curriculum->courseCategories()->create([
    //                 'name' => $categoryData['name'],
    //                 'code' => $categoryData['code'],
    //             ]);

    //             $categoryMap[$categoryData['code']] = $category->id;
    //             Log::debug('Category created', ['code' => $categoryData['code'], 'category_id' => $category->id]);
    //         }

    //         /*11. Courses and Curriculum Courses */
    //         Log::debug('Step 11: Processing Courses and Curriculum Courses');
    //         $courses = $validated['courses'];
    //         $curriculumCourses = $validated['curriculum_courses'];
    //         $courseMap = [];
    //         $departmentId = auth()->user()->department_id;

    //         // First create or update courses
    //         foreach ($courses as $courseData) {
    //             Log::debug('Processing course', $courseData);
    //             // check if the course exist
    //             $existingCourse = DB::table('courses')->where('code', $courseData['code'])->first();

    //             if ($existingCourse) {
    //                 $courseId = $existingCourse->id;
    //                 Log::debug('Course already exists', ['code' => $courseData['code'], 'course_id' => $courseId]);
    //             } else {
    //                 // Create new course
    //                 Log::debug('Creating new course', $courseData);
    //                 DB::table('courses')->insert([
    //                     'code' => $courseData['code'],
    //                     'descriptive_title' => $courseData['descriptive_title'],
    //                     'department_id' => $departmentId,
    //                     'created_at' => now(),
    //                     'updated_at' => now()
    //                 ]);
    //                 $courseId = DB::getPdo()->lastInsertId();
    //                 Log::debug('New course created', ['code' => $courseData['code'], 'course_id' => $courseId]);
    //             }
    //             $courseMap[$courseData['code']] = $courseId;
    //         }

    //         // Create Curriculum Courses
    //         Log::debug('Creating curriculum courses');
    //         foreach ($curriculumCourses as $ccData) {
    //             Log::debug('Processing curriculum course', $ccData);
    //             $semesterKey = $ccData['year'] . '-' . $ccData['sem'];
    //             Log::debug('Semester key', ['key' => $semesterKey, 'semester_id' => $semesterMap[$semesterKey] ?? 'not found']);

    //             if (!isset($semesterMap[$semesterKey])) {
    //                 Log::warning('Semester not found for key', ['key' => $semesterKey, 'available_keys' => array_keys($semesterMap)]);
    //                 continue;
    //             }

    //             if (!isset($courseMap[$ccData['course_code']])) {
    //                 Log::warning('Course not found', ['course_code' => $ccData['course_code'], 'available_courses' => array_keys($courseMap)]);
    //                 continue;
    //             }

    //             if (!isset($categoryMap[$ccData['category_code']])) {
    //                 Log::warning('Category not found', ['category_code' => $ccData['category_code'], 'available_categories' => array_keys($categoryMap)]);
    //                 continue;
    //             }

    //             DB::table('curriculum_courses')->insert([
    //                 'curriculum_id' => $curriculum->id,
    //                 'course_id' => $courseMap[$ccData['course_code']],
    //                 'course_category_id' => $categoryMap[$ccData['category_code']],
    //                 'semester_id' => $semesterMap[$semesterKey],
    //                 'unit' => $ccData['units'],
    //                 'created_at' => now(),
    //                 'updated_at' => now()
    //             ]);
    //             Log::debug('Curriculum course created', [
    //                 'curriculum_id' => $curriculum->id,
    //                 'course_code' => $ccData['course_code'],
    //                 'category_code' => $ccData['category_code']
    //             ]);
    //         }

    //         /*12. Curriculum Course to PO withjson_encode( IRD) */
    //         Log::debug('Step 12: Mapping Curriculum Courses to POs');
    //         $coursePOMappings = $validated['course_po_mappings'];
    //         foreach ($coursePOMappings as $mapping) {
    //             Log::debug('Processing course-PO mapping', $mapping);

    //             if (!isset($courseMap[$mapping['course_code']])) {
    //                 Log::warning('Course not found for PO mapping', ['course_code' => $mapping['course_code']]);
    //                 continue;
    //             }

    //             $courseId = $courseMap[$mapping['course_code']];
    //             $poCode = $mapping['po_code'];
    //             Log::debug('Looking up PO by code', ['po_code' => $poCode]);

    //             // find the PO ID from the name/code
    //             $poId = null;

    //             // find po id
    //             foreach ($poIndexMap as $index => $id) {
    //                 if (isset($pos[$index]['name']) && $pos[$index]['name'] === $poCode) {
    //                     $poId = $id;
    //                     Log::debug('Found PO by code', ['po_code' => $poCode, 'po_id' => $poId]);
    //                     break;
    //                 }
    //             }

    //             if (!$poId) {
    //                 Log::warning('PO not found', ['po_code' => $poCode, 'available_pos' => array_column($pos, 'name')]);
    //                 continue; // skip if poid is not found
    //             }

    //             $curriculumCourseId = DB::table('curriculum_courses')
    //                 ->where('curriculum_id', $curriculum->id)
    //                 ->where('course_id', $courseId)
    //                 ->value('id');

    //             if (!$curriculumCourseId) {
    //                 Log::warning('Curriculum course not found', ['curriculum_id' => $curriculum->id, 'course_id' => $courseId]);
    //                 continue; // skip if curriculum course is not found
    //             }

    //             Log::debug('Found curriculum course', ['curriculum_course_id' => $curriculumCourseId]);

    //             // insert each IRD value
    //             foreach ($mapping['ird'] as $ird) {
    //                 Log::debug('Creating IRD mapping', ['ird' => $ird, 'curriculum_course_id' => $curriculumCourseId, 'po_id' => $poId]);
    //                 DB::table('curriculum_course_po')->insert([
    //                     'curriculum_course_id' => $curriculumCourseId,
    //                     'po_id' => $poId,
    //                     'ird' => $ird,
    //                     'created_at' => now(),
    //                     'updated_at' => now()
    //                 ]);
    //                 Log::debug('IRD mapping created', ['ird' => $ird, 'curriculum_course_id' => $curriculumCourseId, 'po_id' => $poId]);
    //             }
    //         }

    //         Log::debug('All steps completed successfully, committing transaction');
    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Proposal submitted successfully',
    //             'program_id' => $program->id,
    //             'curriculum_id' => $curriculum->id
    //         ], 201);
    //     } catch (Exception $e) {
    //         Log::error('Error in program proposal wizard', [
    //             'exception' => get_class($e),
    //             'message' => $e->getMessage(),
    //             'file' => $e->getFile(),
    //             'line' => $e->getLine(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         DB::rollBack();

    //         return response()->json([
    //             'message' => 'Failed to submit proposal',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}