<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\VisionRequest;
use App\Http\Resources\Api\V1\Admin\VisionResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Vision;

class VisionController extends Controller
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
        $visions = Vision::all();
        return response(
            [
                'data'=>VisionResource::collection($visions),
                'message'=>"vision retrieved successfully",
            ],200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VisionRequest $request)
    {
        
        $vision = Vision::create(
            $request->validated(),
        );

        return response([
            'data' => new VisionResource($vision),
            'message'=> 'vision successfully created',
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $vision = Vision::findOrFail($id);
        
        return response([
            'data' => new VisionResource($vision),
            'message' =>'vision retrieved successfully',
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VisionRequest $request, string $id)
    {

        $vision = Vision::findOrFail($id);
        $vision->update($request->validate());

        return response([
            'data' => new VisionResource($vision),
            'message'=> 'vision updated successfully'
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $vision = Vision::findOrFail($id);
        $vision->delete();

        return response([
            'message' => 'vision deleted successfully'
        ],200);
    }
}
