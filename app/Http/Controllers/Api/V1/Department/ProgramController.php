<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramRequest;
use App\Http\Resources\Api\V1\Department\ProgramResource;
use App\Models\Program;
use Exception;
use Illuminate\Http\Request;

class ProgramController extends Controller
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
            // eager load using with(relationship) function
            // since we eager loaded it in the model already, no need for manual with() function


            $programs = Program::all();

            return ProgramResource::collection($programs)->additional([
                'message' => 'Programs retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve programs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramRequest $request)
    {
        try {
            $program = Program::create($request->validated());

            return (new ProgramResource($program))->additional([
                'message' => 'program created successfully',
            ]);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to create program',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        //
        try {
            return (new ProgramResource($program))->additional([
                'message' => 'Program retrieved successfully'
            ]);
        } catch (Exception $e) {

            return response()->json(
                [
                    'message' => 'failed to retrieve program',
                    'error' => $e->getMessage(),
                ]
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProgramRequest $request, Program $program)
    {
        //
        try {
            $program->update($request->validated());

            return (new ProgramResource($program))->additional([
                'message' => 'program updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update program',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        //
        try {
            $program->delete();

            return response()->json([
                'message' => 'program deleted successfully'
            ]);
        } catch (Exception $e) {
        }
    }
}
