<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\GraduateAttributePeoRequest;
use App\Http\Resources\Api\V1\Department\GraduateAttributePeoResource;
use App\Models\GraduateAttribute;
use Exception;
use Illuminate\Support\Facades\Log;

class GraduateAttributePeoController extends Controller
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
            $gas = GraduateAttribute::with('peos')->get();

            return GraduateAttributePeoResource::collection($gas)->additional([
                'message' => 'All GAs-PEO mappings retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve GAs-PEO mappings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(GraduateAttribute $graduateAttribute)
    {
        try {
            $graduateAttribute->load('peos');
            return (new GraduateAttributePeoResource($graduateAttribute))->additional([
                'message' => 'GA  with mapped PEOs Retrieved successfully '
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve GA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // attache multiple PEOS to a GA
    public function attach(GraduateAttributePeoRequest $request, GraduateAttribute $graduateAttribute)
    {
        try {
            $validated = $request->validated();
            $graduateAttribute->peos()->syncWithoutDetaching($validated['peo_ids']);

            return response()->json([
                'message' => 'PEOs mapped to GA successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'failed to map PEOs to GA',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function detach(GraduateAttributePeoRequest $request, GraduateAttribute $graduateAttribute)
    {
        try {
            $validated = $request->validated();
            $graduateAttribute->peos()->detach($validated['peo_ids']);

            return response()->json([
                'message' => 'PEOs unmapped to GA successfully'
            ], 200);
        } catch (\Throwable $e) {
            //throw $th;
            return response()->json([
                'message' => 'failed to unmap PEOs to GA',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
