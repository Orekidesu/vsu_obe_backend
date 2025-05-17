<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\DepartmentRevisionRequest;
use App\Models\ProgramProposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class DepartmentRevisionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }
    public function handleDepartmentLevelRevision(DepartmentRevisionRequest $request, ProgramProposal $programProposal)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            /**
             * 1️ PROGRAM DETAILS (if exists in the payload)
             */
            if (isset($data['program'])) {
                $programProposal->program()->update([
                    'name' => $data['program']['name'],
                    'abbreviation' => $data['program']['abbreviation'],
                ]);
            }

            /**
             * 2️ PEOs
             */
            $peoMap = [];
            if (isset($data['peos'])) {
                $newPeoIds = collect($data['peos'])->pluck('id')->filter()->toArray();
                $programProposal->peos()->whereNotIn('id', $newPeoIds)->delete();

                foreach ($data['peos'] as $peoData) {
                    $peo = $programProposal->peos()->updateOrCreate(
                        ['id' => $peoData['id'] ?? null],
                        ['statement' => $peoData['statement']]
                    );
                    $peoMap[$peoData['statement']] = $peo->id;
                }
            }

            /**
             * 3️ PEO to Mission Mappings
             */
            if (isset($data['peo_mission_mappings'])) {
                $syncData = collect($data['peo_mission_mappings'])->mapWithKeys(function ($map) use ($peoMap) {
                    $peoId = $map['peo_id'] ?? $peoMap[$map['peo_statement']] ?? null;
                    return [$peoId => ['mission_id' => $map['mission_id']]];
                })->toArray();

                $programProposal->peos()->each(function ($peo) use ($syncData) {
                    $peo->missions()->sync($syncData[$peo->id] ?? []);
                });
            }


            /**
             * 4️ GA to PEO Mappings
             */
            if (isset($data['ga_peo_mappings'])) {
                $syncData = collect($data['ga_peo_mappings'])->mapWithKeys(function ($map) use ($peoMap) {
                    $peoId = $map['peo_id'] ?? $peoMap[$map['peo_statement']] ?? null;
                    return [$peoId => ['ga_id' => $map['ga_id']]];
                })->toArray();

                $programProposal->peos()->each(function ($peo) use ($syncData) {
                    $peo->graduateAttributes()->sync($syncData[$peo->id] ?? []);
                });
            }
            /**
             * 5️ Program Outcomes (POs)
             */
            $poMap = [];
            if (isset($data['pos'])) {
                $newPoIds = collect($data['pos'])->pluck('id')->filter()->toArray();
                $programProposal->pos()->whereNotIn('id', $newPoIds)->delete();

                foreach ($data['pos'] as $poData) {
                    $po = $programProposal->pos()->updateOrCreate(
                        ['id' => $poData['id'] ?? null],
                        ['name' => $poData['name'], 'statement' => $poData['statement']]
                    );
                    $poMap[$poData['name']] = $po->id;
                }
            }


            /**
             * 6️ PO to PEO Mappings
             */
            if (isset($data['po_peo_mappings'])) {
                $syncData = collect($data['po_peo_mappings'])->mapWithKeys(function ($map) use ($poMap, $peoMap) {
                    $poId = $map['po_id'] ?? $poMap[$map['po_name']] ?? null;
                    $peoId = $map['peo_id'] ?? $peoMap[$map['peo_statement']] ?? null;
                    return [$poId => ['peo_id' => $peoId]];
                })->toArray();

                $programProposal->pos()->each(function ($po) use ($syncData) {
                    $po->programEducationalObjectives()->sync($syncData[$po->id] ?? []);
                });
            }
            /**
             * 7️ PO to GA Mappings
             */
            if (isset($data['po_ga_mappings'])) {
                $syncData = collect($data['po_ga_mappings'])->mapWithKeys(function ($map) use ($poMap) {
                    $poId = $map['po_id'] ?? $poMap[$map['po_name']] ?? null;
                    return [$poId => ['ga_id' => $map['ga_id']]];
                })->toArray();

                $programProposal->pos()->each(function ($po) use ($syncData) {
                    $po->gas()->sync($syncData[$po->id] ?? []);
                });
            }
            /**
             * 8 Curriculum
             */
            if (isset($data['curriculum'])) {
                $programProposal->curriculum()->update(['name' => $data['curriculum']['name']]);
            }

            /**
             * 9 Course Categories
             */
            $categoryMap = [];
            if (isset($data['course_categories'])) {
                $newCategoryIds = collect($data['course_categories'])->pluck('id')->filter()->toArray();

                // Delete categories that are not in the request
                $programProposal->curriculum->courseCategories()->whereNotIn('id', $newCategoryIds)->delete();

                // Create or update categories and map them
                foreach ($data['course_categories'] as $categoryData) {
                    $category = $programProposal->curriculum->courseCategories()->updateOrCreate(
                        ['id' => $categoryData['id'] ?? null],
                        [
                            'name' => $categoryData['name'],
                            'code' => $categoryData['code']
                        ]
                    );
                    // Map to track for Curriculum Courses
                    $categoryMap[$categoryData['code']] = $category->id;
                }
            }

            /**
             * 10 Curriculum Courses
             */
            $curriculumCourseMap = [];
            if (isset($data['curriculum_courses'])) {
                $newCurriculumCourseIds = collect($data['curriculum_courses'])->pluck('id')->filter()->toArray();

                // Delete courses that are not in the payload
                $programProposal->curriculum->curriculumCourses()->whereNotIn('id', $newCurriculumCourseIds)->delete();

                foreach ($data['curriculum_courses'] as $courseData) {
                    $curriculumCourse = $programProposal->curriculum->curriculumCourses()->updateOrCreate(
                        ['id' => $courseData['id'] ?? null],
                        [
                            'course_id' => $courseData['course_id'],
                            'course_category_id' => $categoryMap[$courseData['category_code']] ?? $courseData['course_category_id'],
                            'semester_id' => $courseData['semester_id'],
                            'unit' => $courseData['unit']
                        ]
                    );
                    $curriculumCourseMap[$courseData['course_id']] = $curriculumCourse->id;
                }
            }

            /**
             * 11 Curriculum Course to PO Mappings
             */
            if (isset($data['course_po_mappings'])) {
                $syncData = collect($data['course_po_mappings'])->map(function ($mapping) use ($curriculumCourseMap) {
                    $curriculumCourseId = $mapping['curriculum_course_id'] ?? $curriculumCourseMap[$mapping['course_id']] ?? null;
                    return [
                        'curriculum_course_id' => $curriculumCourseId,
                        'po_id' => $mapping['po_id'],
                        'ird' => json_encode($mapping['ird']),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                })->filter(function ($mapping) {
                    return !is_null($mapping['curriculum_course_id']); // Ensure valid IDs
                });

                // Sync operation: Deletes missing, updates existing, and inserts new
                DB::table('curriculum_course_po')->whereIn('curriculum_course_id', array_column($syncData->toArray(), 'curriculum_course_id'))->delete();
                DB::table('curriculum_course_po')->insert($syncData->toArray());
            }

            DB::commit();
            return response()->json(['message' => 'Department revision handled successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process department revision.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
