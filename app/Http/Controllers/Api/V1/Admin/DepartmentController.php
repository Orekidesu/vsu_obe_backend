<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\DepartmentRequest;
use App\Http\Resources\Api\V1\Admin\DepartmentResource;
use App\Models\Department;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class DepartmentController extends Controller
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
            // $departments = Department::all();
            $departments = Department::with('program')->get();

            return response()->json([
                'data' => DepartmentResource::collection($departments),
                'message' => 'Departments retrieved successfully',

            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieved departments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request)
    {
        try {
            $department = Department::create($request->validated());

            return response()->json([
                'data' => new DepartmentResource($department),
                'message' => 'department created successfully',
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create department',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        try {

            return response()->json([
                'data' => new DepartmentResource($department),
                'message' => 'department retrieved successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'department not found',

            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve deparment',
                'error' => $e->getMessage(),
            ], 500);
        }
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        //
        try {
            $department->update($request->validated());

            return response()->json([
                'data' => new DepartmentResource($department),
                'message' => 'department updated successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'department not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to updated department',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        try {
            $department->delete();

            return response()->json([
                'message' => 'department deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'department not found',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete department',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
