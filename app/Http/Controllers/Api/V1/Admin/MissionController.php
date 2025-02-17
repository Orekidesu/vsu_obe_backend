<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\MissionRequest;
use App\Http\Resources\Api\V1\Admin\MissionResource;
use Exception;
use App\Models\Mission;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class MissionController extends Controller
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
        //
        try{
            $missions = Mission::all();

            return response()->json([
                'data'=> MissionResource::collection($missions),
                'message' =>'missions retrieved successfully'
            ],200);

        }catch(Exception $e){
            
            return response()->json([
                'message'=>'failed to retrieved missions',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MissionRequest $request)
    {

        try{
            $vision = Mission::create($request->validated());

            return response()->json([
                'data'=>new MissionResource($vision),
                'message'=>'mission created successfully'
            ],201);

        }catch(Exception $e){

            return response()->json([
                'message'=>'failed to create mission',
                'error'=>$e->getMessage(),
            ],500);

        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Mission $mission)
    {
        //
        try{
            
            return response()->json([
                'data'=>new MissionResource($mission),
                'message'=>'mission retrieved successfully',
            ],200);

            
        }catch(Exception $e){

            return response()->json([
                'message'=>$e instanceof ModelNotFoundException ? 'mission not found' : 'failed to retrieved mission',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MissionRequest $request, Mission $mission)
    {
        try{
            $mission->update($request->validated());
            
            return response()->json([
                'data'=>new MissionResource($mission),
                'message'=>'mission updated successfully',
            ],200);

        }catch(Exception $e){

            return response()->json([
                'message'=> $e instanceof ModelNotFoundException ? 'mission not found' : 'failed to update mission',
                'error'=>$e->getMessage(),
            ],500);
            
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mission $mission)
    {
        try{
            $mission->delete();

            return response()->json([
                'message'=>'mission deleted successfully',
            ],200);


        }catch(Exception $e){

            return response()->json([
                'message'=> $e instanceof ModelNotFoundException ? 'mission not found' : 'failed to delete mission',
                'error' => $e->getMessage(),
            ]);
        }
        //
    }
}