<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Department\SemesterResource;
use App\Models\Semester;
use Exception;
use Illuminate\Http\Request;
use App\Http\Requests\Api\V1\Department\SemesterRequest;

class SemesterController extends Controller
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
            $semesters = Semester::all();

            return SemesterResource::collection($semesters)->additional([
                'message' => 'semesters retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve semesters',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SemesterRequest $request)
    {
        //
        try {
            $newSemester = Semester::create($request->validated());

            return (new SemesterResource($newSemester))->additional([
                'message' => 'semester created successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Semester $semester)
    {
        //
        try {
            return (new SemesterResource($semester))->additional([
                'message' => 'semester retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SemesterRequest $request, Semester $semester)
    {
        //
        try {

            $semester->update($request->validated());
            return (new SemesterResource($semester))->additional([
                'message' => 'semester updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Semester $semester)
    {
        //
        try {
            $semester->delete();

            return response()->json([
                'message' => 'semester deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete semester',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
