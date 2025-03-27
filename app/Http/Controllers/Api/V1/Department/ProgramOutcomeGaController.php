<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramOutcomeGaRequest;
use App\Http\Requests\Api\V1\Department\ProgramOutcomePeoRequest;
use App\Http\Resources\Api\V1\Department\ProgramOutcomeGaResource;
use App\Models\ProgramOutcome;
use Exception;
use Illuminate\Http\Request;

class ProgramOutcomeGaController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }

    public function index()
    {
        try {
            $pos = ProgramOutcome::with('gas')->get();

            return ProgramOutcomeGaResource::collection($pos)->additional([
                'message' => 'All PO-GA mappings retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieved PO-GA mappings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ProgramOutcome $programOutcome)
    {
        try {
            $programOutcome->load('gas');

            return (new ProgramOutcomeGaResource($programOutcome))->additional([
                'message' => 'PO with mapped GAs retrieved successfulyy'
            ]);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to retrieve GA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function attach(ProgramOutcomeGaRequest $request, ProgramOutcome $programOutcome)
    {
        try {
            $validated = $request->validated();

            $programOutcome->gas()->syncWithoutDetaching($validated['ga_ids']);

            return response()->json([
                'message' => 'GAs mapped to PO successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to map GAs to PO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function detach(ProgramOutcomeGaRequest $request, ProgramOutcome $programOutcome)
    {
        try {
            $validated = $request->validated();

            $programOutcome->gas()->detach($validated['ga_ids']);

            return response()->json([
                'message' => 'GAs unmapped to PO successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to unmap GAs to PO',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
