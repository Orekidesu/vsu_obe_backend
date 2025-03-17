<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\FacultyRequest;
use App\Http\Resources\Api\V1\Admin\FacultyResource;
use App\Models\Faculty;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\TryCatch;

class FacultyController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Admin');
    }

    public function index()
    {
        try {
            $faculties = Faculty::with('department')->without('department.faculty')->get();

            return response()->json([
                'data' => FacultyResource::collection($faculties),
                'message' => 'facultyies retrieved successfully',

            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to retrieved faculties',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FacultyRequest $request)
    {
        try {
            $faculty = Faculty::create($request->validated());

            return response()->json([
                'data' => new FacultyResource($faculty),
                'message' => 'faculty created successfully',
            ], 201);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to create faculty',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Faculty $faculty)
    {
        try {
            return response()->json([
                'data' => new FacultyResource($faculty),
                'message' => 'faculty retrieved successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'faculty not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve faculty',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FacultyRequest $request, Faculty $faculty)
    {
        try {
            $faculty->update($request->validated());

            return response()->json([
                'data' => new FacultyResource($faculty),
                'message' => 'faculty updated successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'faculty not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to updated faculty',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faculty $faculty)
    {

        try {
            $faculty->delete();

            return response()->json([
                'message' => 'faculty deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'faculty not found',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete faculty',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
