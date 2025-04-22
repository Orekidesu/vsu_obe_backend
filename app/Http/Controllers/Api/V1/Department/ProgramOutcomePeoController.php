<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramOutcomePeoRequest;
use App\Http\Resources\Api\V1\Department\ProgramOutcomePeoResource;
use App\Models\ProgramOutcome;
use Exception;
use Illuminate\Http\Request;

class ProgramOutcomePeoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }

    public function index()
    {
        try {
            $pos = ProgramOutcome::with('peos')->get();

            return ProgramOutcomePeoResource::collection($pos)->additional([
                'message' => 'All Po-PEO mappings retrieved successfully'
            ]);
        } catch (Exception $e) {

            return response()->json([
                'message' => 'failed to retrieve PO-PEO mappings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ProgramOutcome $programOutcome)
    {
        try {
            $programOutcome->load('peos');

            return (new ProgramOutcomePeoResource($programOutcome))->additional([
                'message' => 'PO with mapped PEOs retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve PO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function attach(ProgramOutcomePeoRequest $request, ProgramOutcome $programOutcome)
    {
        try {
            $validated = $request->validated();

            $programOutcome->peos()->syncWithoutDetaching($validated['peo_ids']);

            return response()->json([
                'message' => 'PEOs mapped to PO successfully'
            ], 200);
        } catch (Exception $e) {

            return response()->json([
                'message' => "failed to map PEOs to PO",
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function detach(ProgramOutcomePeoRequest $request, ProgramOutcome $programOutcome)
    {
        try {
            $validated = $request->validated();

            $programOutcome->peos()->detach($validated['peo_ids']);

            return response()->json([
                'message' => 'PEOs unmapped to PO successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to unmap PEO to PO',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
