<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\DepartmentRevisionRequest;
use App\Models\ProgramProposal;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\CourseCategory;

class DepartmentRevisionController extends Controller
{
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
            // Track IDs for newly created entities and existing ones
            $entityMap = [
                'peos' => [], // Maps both string and numeric IDs to real DB IDs
                'pos' => [],
                'categories' => [],
                'courses' => []
            ];

            /**
             * 1️ PROGRAM DETAILS
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
            if (isset($data['peos'])) {
                // Get numeric IDs only for deletion check
                $numericPeoIds = collect($data['peos'])
                    ->pluck('id')
                    ->filter(function ($id) {
                        return is_numeric($id);
                    })
                    ->toArray();

                // Delete PEOs not in the payload
                $programProposal->peos()->whereNotIn('id', $numericPeoIds)->delete();

                foreach ($data['peos'] as $peoData) {
                    $isNew = !is_numeric($peoData['id']);

                    if ($isNew) {
                        // Create new PEO (string ID)
                        $peo = $programProposal->peos()->create([
                            'statement' => $peoData['statement']
                        ]);
                    } else {
                        // Update existing PEO (numeric ID)
                        $peo = $programProposal->peos()->updateOrCreate(
                            ['id' => $peoData['id']],
                            ['statement' => $peoData['statement']]
                        );
                    }

                    // Map both original ID and statement for lookup
                    $entityMap['peos'][$peoData['id']] = $peo->id;
                    $entityMap['peos'][$peoData['statement']] = $peo->id;
                }
            }

            /**
             * 3️ PEO to Mission Mappings
             */
            if (isset($data['peo_mission_mappings'])) {
                // Group mappings by resolved PEO ID
                $mappingsByPeoId = collect($data['peo_mission_mappings'])
                    ->groupBy(function ($map) use ($entityMap) {
                        $peoId = $map['peo_id'];
                        // Resolve the PEO ID (could be string for new or numeric for existing)
                        return $entityMap['peos'][$peoId] ?? $peoId;
                    });

                foreach ($mappingsByPeoId as $peoId => $mappings) {
                    // Skip invalid PEO IDs
                    if (!is_numeric($peoId)) continue;

                    // Get all mission IDs for this PEO
                    $missionIds = $mappings->pluck('mission_id')->toArray();

                    // Sync missions for this PEO
                    $peo = $programProposal->peos()->find($peoId);
                    if ($peo) {
                        $pivotData = [];
                        foreach ($missionIds as $missionId) {
                            $pivotData[$missionId] = [
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                        $peo->missions()->sync($pivotData);
                    }
                }
            }

            /**
             * 4️ GA to PEO Mappings
             */
            if (isset($data['ga_peo_mappings'])) {
                // Group mappings by resolved PEO ID
                $mappingsByPeoId = collect($data['ga_peo_mappings'])
                    ->groupBy(function ($map) use ($entityMap) {
                        $peoId = $map['peo_id'];
                        return $entityMap['peos'][$peoId] ?? $peoId;
                    });

                foreach ($mappingsByPeoId as $peoId => $mappings) {
                    if (!is_numeric($peoId)) continue;

                    $gaIds = $mappings->pluck('ga_id')->toArray();

                    $peo = $programProposal->peos()->find($peoId);
                    if ($peo) {
                        $pivotData = [];
                        foreach ($gaIds as $gaId) {
                            $pivotData[$gaId] = [
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                        $peo->gas()->sync($pivotData);
                    }
                }
            }

            /**
             * 5️ Program Outcomes (POs)
             */
            if (isset($data['pos'])) {
                // Get numeric IDs only for deletion check
                $numericPoIds = collect($data['pos'])
                    ->pluck('id')
                    ->filter(function ($id) {
                        return is_numeric($id);
                    })
                    ->toArray();

                // Delete POs not in the payload
                $programProposal->pos()->whereNotIn('id', $numericPoIds)->delete();

                foreach ($data['pos'] as $poData) {
                    $isNew = !is_numeric($poData['id']);

                    if ($isNew) {
                        // Create new PO (string ID)
                        $po = $programProposal->pos()->create([
                            'name' => $poData['name'],
                            'statement' => $poData['statement']
                        ]);
                    } else {
                        // Update existing PO (numeric ID)
                        $po = $programProposal->pos()->updateOrCreate(
                            ['id' => $poData['id']],
                            ['name' => $poData['name'], 'statement' => $poData['statement']]
                        );
                    }

                    // Map both original ID and name for lookup
                    $entityMap['pos'][$poData['id']] = $po->id;
                    $entityMap['pos'][$poData['name']] = $po->id;
                }
            }

            /**
             * 6️ PO to PEO Mappings
             */
            if (isset($data['po_peo_mappings'])) {
                // Get all PO IDs for this proposal
                $allPoIds = $programProposal->pos()->pluck('id')->toArray();

                // Organize mappings by resolved PO ID
                $poToPeoMappings = [];
                foreach ($data['po_peo_mappings'] as $mapping) {
                    // Resolve both PO and PEO IDs
                    $poId = $entityMap['pos'][$mapping['po_id']] ?? $mapping['po_id'];
                    $peoId = $entityMap['peos'][$mapping['peo_id']] ?? $mapping['peo_id'];

                    if (is_numeric($poId) && is_numeric($peoId)) {
                        if (!isset($poToPeoMappings[$poId])) {
                            $poToPeoMappings[$poId] = [];
                        }
                        $poToPeoMappings[$poId][] = $peoId;
                    }
                }

                // Process ALL POs
                foreach ($allPoIds as $poId) {
                    $po = $programProposal->pos()->find($poId);
                    if ($po) {
                        $peoIds = $poToPeoMappings[$poId] ?? [];

                        $pivotData = [];
                        foreach ($peoIds as $peoId) {
                            $pivotData[$peoId] = [
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }

                        $po->peos()->sync($pivotData);
                    }
                }
            }

            /**
             * 7️ PO to GA Mappings
             */
            if (isset($data['po_ga_mappings'])) {
                // Get all PO IDs for this proposal
                $allPoIds = $programProposal->pos()->pluck('id')->toArray();

                // Organize mappings by resolved PO ID
                $poToGaMappings = [];
                foreach ($data['po_ga_mappings'] as $mapping) {
                    $poId = $entityMap['pos'][$mapping['po_id']] ?? $mapping['po_id'];
                    $gaId = $mapping['ga_id'];

                    if (is_numeric($poId) && is_numeric($gaId)) {
                        if (!isset($poToGaMappings[$poId])) {
                            $poToGaMappings[$poId] = [];
                        }
                        $poToGaMappings[$poId][] = $gaId;
                    }
                }

                // Process ALL POs
                foreach ($allPoIds as $poId) {
                    $po = $programProposal->pos()->find($poId);
                    if ($po) {
                        $gaIds = $poToGaMappings[$poId] ?? [];

                        $pivotData = [];
                        foreach ($gaIds as $gaId) {
                            $pivotData[$gaId] = [
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }

                        $po->gas()->sync($pivotData);
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
            if (isset($data['course_categories'])) {
                foreach ($data['course_categories'] as $categoryData) {
                    $isNew = !is_numeric($categoryData['id']);

                    // First check if category with this code already exists
                    $existingCategory = CourseCategory::where('code', $categoryData['code'])->first();

                    if ($existingCategory) {
                        // Update the existing category
                        $existingCategory->update([
                            'name' => $categoryData['name']
                        ]);
                        $category = $existingCategory;
                    } else if ($isNew) {
                        // Create new category (string ID)
                        $category = CourseCategory::create([
                            'name' => $categoryData['name'],
                            'code' => $categoryData['code']
                        ]);
                    } else {
                        // Update existing category (numeric ID)
                        $category = CourseCategory::updateOrCreate(
                            ['id' => $categoryData['id']],
                            [
                                'name' => $categoryData['name'],
                                'code' => $categoryData['code']
                            ]
                        );
                    }

                    // Map both ID and code
                    $entityMap['categories'][$categoryData['id']] = $category->id;
                    $entityMap['categories'][$categoryData['code']] = $category->id;
                }
            }

            /**
             * 10 Curriculum Courses
             */
            if (isset($data['curriculum_courses'])) {
                // Gather all course IDs used in the curriculum
                $courseIds = collect($data['curriculum_courses'])->pluck('course_id')->unique()->toArray();

                // Find which ones don't exist
                $existingCourseIds = DB::table('courses')->whereIn('id', $courseIds)->pluck('id')->toArray();
                $missingCourseIds = array_diff($courseIds, $existingCourseIds);

                // Create the missing courses 
                foreach ($data['curriculum_courses'] as $courseData) {
                    if (in_array($courseData['course_id'], $missingCourseIds)) {
                        // Make sure we have the required fields for a course
                        if (isset($courseData['course_code']) && isset($courseData['course_title'])) {
                            // Create the missing course
                            $course = \App\Models\Course::create([
                                'id' => $courseData['course_id'],
                                'code' => $courseData['course_code'],
                                'descriptive_title' => $courseData['course_title']
                            ]);

                            // Log that we created this course
                            // \Log::info("Created missing course: {$course->code} - {$course->descriptive_title}");
                        } else {
                            // Log a warning if course data is incomplete
                            // \Log::warning("Cannot create course with ID {$courseData['course_id']} - missing code or title");
                        }
                    }
                }
            }

            /**
             * 11 Curriculum Course to PO Mappings
             */
            if (isset($data['course_po_mappings'])) {
                // Get all curriculum course IDs
                $allCurriculumCourseIds = $programProposal->curriculum->curriculumCourses()->pluck('id')->toArray();

                // Group mappings by resolved curriculum course ID
                $mappingsByCurriculumCourseId = collect($data['course_po_mappings'])
                    ->groupBy(function ($mapping) use ($entityMap) {
                        // Resolve curriculum course ID
                        $courseId = $mapping['curriculum_course_id'];
                        return $entityMap['courses'][$courseId] ?? $courseId;
                    });

                // Process ALL courses
                foreach ($allCurriculumCourseIds as $curriculumCourseId) {
                    $curriculumCourse = $programProposal->curriculum->curriculumCourses()->find($curriculumCourseId);
                    if ($curriculumCourse) {
                        $pivotData = [];

                        if (isset($mappingsByCurriculumCourseId[$curriculumCourseId])) {
                            $mappings = $mappingsByCurriculumCourseId[$curriculumCourseId];
                            foreach ($mappings as $mapping) {
                                // Resolve PO ID
                                $poId = $entityMap['pos'][$mapping['po_id']] ?? $mapping['po_id'];

                                if (is_numeric($poId)) {
                                    $pivotData[$poId] = [
                                        'ied' => json_encode($mapping['ied']),
                                        'created_at' => now(),
                                        'updated_at' => now()
                                    ];
                                }
                            }
                        }

                        $curriculumCourse->pos()->sync($pivotData);
                    }
                }
            }

            // Now make the proposal pending again
            $programProposal->update([
                'status' => 'pending'
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Department revision handled successfully.',
                'entity_map' => $entityMap // Return ID mappings for frontend reference
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to process department revision.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
