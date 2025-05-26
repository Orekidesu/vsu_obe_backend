<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramOutcomeRequest;
use App\Http\Resources\Api\V1\Department\ProgramOutcomeResource;
use App\Models\ProgramOutcome;
use Exception;
use Illuminate\Http\Request;

class ProgramOutcomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }

    public function index(Request $request)
    {
        try {
            $query = ProgramOutcome::with('programProposal');
            // $pos = ProgramOutcome::all();

            if ($request->has('program_proposal_id')) {
                $query->where('program_proposal_id', $request->program_proposal_id);
            }

            $pos = $query->get();

            return ProgramOutcomeResource::collection($pos)->additional([
                'message' => 'POS retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve POS',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramOutcomeRequest $request)
    {
        try {
            $newPO = ProgramOutcome::create($request->validated());

            return (new ProgramOutcomeResource($newPO))->additional([
                'message' => 'PO created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create PO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgramOutcome $programOutcome)
    {

        try {
            return (new ProgramOutcomeResource($programOutcome))->additional([
                'message' => 'PO retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve PO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProgramOutcomeRequest $request, ProgramOutcome $programOutcome)
    {
        try {
            $programOutcome->update($request->validated());

            return (new ProgramOutcomeResource($programOutcome))->additional([
                'message' => 'PO updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to uppdate PO',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProgramOutcome $programOutcome)
    {

        try {
            $programOutcome->delete();

            return response()->json([
                'message' => 'PO deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to delete PO',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
