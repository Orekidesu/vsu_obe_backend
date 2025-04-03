<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\CurriculumCourseRequest;
use App\Http\Resources\Api\V1\Department\CurriculumCourseResource;
use App\Models\CurriculumCourse;
use Exception;
use Illuminate\Support\Facades\DB;

class CurriculumCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }

    public function index()
    {
        //
        try {
            $curriculumCourses = CurriculumCourse::with(['curriculum', 'course', 'semester', 'courseCategory'])->get();

            return CurriculumCourseResource::collection($curriculumCourses)->additional([
                'message' => 'curriculum courses retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve curriculum courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CurriculumCourseRequest $request)
    {
        try {
            $newCurriculumCourse = DB::transaction(function () use ($request) {
                return CurriculumCourse::create($request->validated());
            });

            return (new CurriculumCourseResource($newCurriculumCourse))->additional([
                'message' => 'curriculum resource created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create curriculum courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CurriculumCourse $curriculumCourse)
    {

        try {
            $curriculumCourse->load(['curriculum', 'course', 'courseCategory', 'semester']);

            return (new CurriculumCourseResource($curriculumCourse))->additional([
                'message' => 'Curriculum course retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurriculumCourseRequest $request, CurriculumCourse $curriculumCourse)
    {
        try {
            $curriculumCourse->update($request->validated());

            return response()->json([
                'message' => 'Curriculum course updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CurriculumCourse $curriculumCourse)
    {
        //
        try {
            $curriculumCourse->delete();

            return response()->json([
                'message' => 'Curriculum course deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}