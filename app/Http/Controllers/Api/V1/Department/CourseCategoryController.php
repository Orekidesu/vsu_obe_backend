<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\CourseCategoryRequest;
use App\Http\Resources\Api\V1\Department\CourseCategoryResource;
use App\Http\Resources\Api\V1\Department\CurriculumResource;
use App\Models\CourseCategory;
use Exception;
use Illuminate\Http\Request;

class CourseCategoryController extends Controller
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
        try {
            $courseCategories = CourseCategory::with('curriculum')->get();

            return CourseCategoryResource::collection($courseCategories)->additional([
                'message' => 'course categories retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve course categories',
                'error' => $e->getMessage(),
            ]);
        }

        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseCategoryRequest $request)
    {
        //
        try {
            $newCourse = CourseCategory::create($request->validated());

            return (new CourseCategoryResource($newCourse))->additional([
                'message' => 'course category created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create course category',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CourseCategory $courseCategory)
    {
        //
        try {
            $courseCategory->load('curriculum');
            return (new CourseCategoryResource($courseCategory))->additional([
                'message' => 'course category retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve course category',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseCategoryRequest $request, CourseCategory $courseCategory)
    {
        //
        try {
            $courseCategory->update($request->validated());
            $courseCategory->load('curriculum');
            return (new CourseCategoryResource($courseCategory))->additional([
                'message' => 'course category updated successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update course category',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseCategory $courseCategory)
    {
        //
        try {
            $courseCategory->delete();
            return response()->json([
                'message' => 'course category deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete course category',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
