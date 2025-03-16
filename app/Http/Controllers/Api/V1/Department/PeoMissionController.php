<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\PeoMissionRequest;
use App\Models\ProgramEducationalObjective;
use Illuminate\Http\Request;
use Exception;

class PeoMissionController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }


    // get all mapped peos with their corresponding missions
    public function index()
    {
        try {

            return response()->json([
                'data' => ProgramEducationalObjective::with('missions')->get(),
                'message' => 'All PEO-Missions mappings retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieved PEO-Missions Mappings',
                'error' => $e->getMessage(),

            ], 500);
        }
    }

    // get missions for a specific PEO
    public function show(ProgramEducationalObjective $peo)
    {
        try {
            $peo->load('missions');
            return response()->json([
                'data' => $peo,
                'message' => 'PEO with mapped missions retrieved successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to fetch PEO with mapped missions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //attach multiple missions to a peo (Bulk Insert)
    public function attach(PeoMissionRequest $request, ProgramEducationalObjective $peo)
    {

        try {
            $validated = $request->validated();
            $peo->missions()->syncWithoutDetaching($validated['mission_ids']);

            return response()->json([
                'message' => 'Missions mapped to PEO successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to map missions to PEO',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function detach(PeoMissionRequest $request, ProgramEducationalObjective $peo)
    {
        try {
            $validated = $request->validated();
            $peo->missions()->detach($validated['mission_ids']);

            return response()->json([
                'message' => 'Missions unmapped to PEO successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to unmap missions to PEO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // Next step:
    /* 
        1. Make Tests on both the request and the controller
        2. Understand the attach and detach function
    */
}
