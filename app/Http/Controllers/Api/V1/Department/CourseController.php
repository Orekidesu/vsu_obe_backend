<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\CourseRequest;
use App\Http\Resources\Api\V1\Department\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;
use Exception;

class CourseController extends Controller
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
            $courses = Course::all();

            return CourseResource::collection($courses)->additional([
                'message' => 'courses rettrived successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CourseRequest $request)
    {
        try {
            $newCourse = Course::create($request->validated());

            return (new CourseResource($newCourse))->additional([
                'message' => 'course created successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create course',


                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Course $course)
    {
        try {
            $course->load('department');

            return (new CourseResource($course))->additional([
                'message' => 'course retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve course',
                'error' => $e->getMessage(),
            ], 500);
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CourseRequest $request, Course $course)
    {
        //
        try {
            $course->update($request->validated());
            $course->load('department');

            return (new CourseResource($course))->additional([
                'message' => 'course updated successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course)
    {
        //
        try {
            $course->delete();

            return response()->json([
                'message' => 'course deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}