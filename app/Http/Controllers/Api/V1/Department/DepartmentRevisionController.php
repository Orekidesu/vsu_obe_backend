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
                // Group mappings by PEO ID
                $mappingsByPeoId = collect($data['peo_mission_mappings'])
                    ->groupBy(function ($map) use ($peoMap) {
                        return $map['peo_id'] ?? $peoMap[$map['peo_statement']] ?? null;
                    });

                foreach ($mappingsByPeoId as $peoId => $mappings) {
                    if (!$peoId) continue; // Skip if no valid PEO ID

                    // Get all mission IDs for this PEO
                    $missionIds = $mappings->pluck('mission_id')->toArray();

                    // Sync missions for this PEO
                    $peo = $programProposal->peos()->find($peoId);
                    if ($peo) {
                        $peo->missions()->sync($missionIds);
                    }
                }
            }


            /**
             * 4️ GA to PEO Mappings
             */
            if (isset($data['ga_peo_mappings'])) {
                // Group mappings by PEO ID
                $mappingsByPeoId = collect($data['ga_peo_mappings'])
                    ->groupBy(function ($map) use ($peoMap) {
                        return $map['peo_id'] ?? $peoMap[$map['peo_statement']] ?? null;
                    });

                foreach ($mappingsByPeoId as $peoId => $mappings) {
                    if (!$peoId) continue;

                    $gaIds = $mappings->pluck('ga_id')->toArray();

                    $peo = $programProposal->peos()->find($peoId);
                    if ($peo) {
                        $peo->gas()->sync($gaIds);
                    }
                }
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
                // Get all PO IDs for this proposal
                $allPoIds = $programProposal->pos()->pluck('id')->toArray();

                // Organize mappings by PO ID
                $poToPeoMappings = [];
                foreach ($data['po_peo_mappings'] as $mapping) {
                    $poId = $mapping['po_id'] ?? $poMap[$mapping['po_name']] ?? null;
                    $peoId = $mapping['peo_id'] ?? $peoMap[$mapping['peo_statement']] ?? null;

                    if ($poId && $peoId) {
                        if (!isset($poToPeoMappings[$poId])) {
                            $poToPeoMappings[$poId] = [];
                        }
                        $poToPeoMappings[$poId][] = $peoId;
                    }
                }

                // Process ALL POs, with empty arrays for those not in payload
                foreach ($allPoIds as $poId) {
                    $po = $programProposal->pos()->find($poId);
                    if ($po) {
                        $peoIds = $poToPeoMappings[$poId] ?? [];
                        $po->peos()->sync($peoIds);
                    }
                }
            }
            /**
             * 7️ PO to GA Mappings
             */
            if (isset($data['po_ga_mappings'])) {
                // Get all PO IDs for this proposal
                $allPoIds = $programProposal->pos()->pluck('id')->toArray();

                // Organize mappings by PO ID
                $poToGaMappings = [];
                foreach ($data['po_ga_mappings'] as $mapping) {
                    $poId = $mapping['po_id'] ?? $poMap[$mapping['po_name']] ?? null;
                    $gaId = $mapping['ga_id'];

                    if ($poId && $gaId) {
                        if (!isset($poToGaMappings[$poId])) {
                            $poToGaMappings[$poId] = [];
                        }
                        $poToGaMappings[$poId][] = $gaId;
                    }
                }

                // Process ALL POs, with empty arrays for those not in payload
                foreach ($allPoIds as $poId) {
                    $po = $programProposal->pos()->find($poId);
                    if ($po) {
                        $gaIds = $poToGaMappings[$poId] ?? [];
                        $po->gas()->sync($gaIds);
                    }
                }
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
                // Get all curriculum course IDs
                $allCurriculumCourseIds = $programProposal->curriculum->curriculumCourses()->pluck('id')->toArray();

                // Group mappings by curriculum course ID
                $mappingsByCurriculumCourseId = collect($data['course_po_mappings'])
                    ->groupBy(function ($mapping) use ($curriculumCourseMap) {
                        return $mapping['curriculum_course_id'] ?? $curriculumCourseMap[$mapping['course_id']] ?? null;
                    });

                // Process ALL courses, with empty arrays for those not in payload
                foreach ($allCurriculumCourseIds as $curriculumCourseId) {
                    $curriculumCourse = $programProposal->curriculum->curriculumCourses()->find($curriculumCourseId);
                    if ($curriculumCourse) {
                        $pivotData = [];

                        if (isset($mappingsByCurriculumCourseId[$curriculumCourseId])) {
                            $mappings = $mappingsByCurriculumCourseId[$curriculumCourseId];
                            foreach ($mappings as $mapping) {
                                $pivotData[$mapping['po_id']] = [
                                    'ied' => json_encode($mapping['ied']),
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                        }

                        $curriculumCourse->pos()->sync($pivotData);
                    }
                }
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
