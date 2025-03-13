<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramEducationalObjectiveRequest;
use App\Http\Resources\Api\V1\Department\ProgramEducationalObjectiveResource;
use App\Models\ProgramEducationalObjective;
use Exception;
use Illuminate\Http\Request;

class ProgramEducationalObjectiveController extends Controller
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

            $PEOS = ProgramEducationalObjective::all();

            return ProgramEducationalObjectiveResource::collection($PEOS)->additional([
                'message' => 'PEOs retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve PEOs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramEducationalObjectiveRequest $request)
    {
        try {
            $peo = ProgramEducationalObjective::create($request->validated());

            return (new ProgramEducationalObjectiveResource($peo))->additional([
                'message' => 'PEO created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create PEO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgramEducationalObjective $programEducationalObjective)
    {
        try {
            return (new ProgramEducationalObjectiveResource($programEducationalObjective))->additional([
                'message' => 'PEO retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve PEO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProgramEducationalObjectiveRequest $request, ProgramEducationalObjective $programEducationalObjective)
    {
        try {
            $programEducationalObjective->update($request->validated());
            return (new ProgramEducationalObjectiveResource($programEducationalObjective))->additional([
                'message' => 'PEO updated successfully',
            ]);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to update PEO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgramEducationalObjective $programEducationalObjective)
    {
        try {
            $programEducationalObjective->delete();

            return response()->json([
                'message' => 'PEO deleted successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete PEO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
