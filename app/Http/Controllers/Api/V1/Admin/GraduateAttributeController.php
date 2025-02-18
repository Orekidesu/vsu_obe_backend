<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\GraduateAttributeRequest;
use App\Http\Resources\Api\V1\Admin\GraduateAttributeResource;
use Exception;
use App\Models\GraduateAttribute;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GraduateAttributeController extends Controller
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
            $graduateAttributes = GraduateAttribute::All();

            return response()->json([
                'data'=> GraduateAttributeResource::collection($graduateAttributes),
                'message'=>'graduate attributes successfully retrieved',
            ],200);

        }catch(Exception $e){
            return response()->json([
                'message'=>'failed to retrieve graduate attributes',
                'error'=> $e->getMessage(),
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GraduateAttributeRequest $request)
    {
        try{
            $graduateAttribute = GraduateAttribute::create($request->validated());

            return response()->json([
                'data'=> new GraduateAttributeResource($graduateAttribute),
                'message'=>'graduate attribute created successfully',
            ],201);

        }catch(Exception $e){
            return response()->json([
                'message'=>'failed to create graduate attribute',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(GraduateAttribute $graduateAttribute)
    {
        try{
            return response()->json([
                'data'=>new GraduateAttributeResource($graduateAttribute),
                'message'=>'graduate attribute retrieved successfully',
            ],200);
        }catch(Exception $e){
            return response()->json([
                'message'=>$e instanceof ModelNotFoundException ? 'graduate attribute not found' : 'failed to retrieve graduate attribute',
                'error'=> $e->getMessage(),
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(GraduateAttributeRequest $request, GraduateAttribute $graduateAttribute)
    {
        try{
            $graduateAttribute->update($request->validated());

            return response()->json([
                'data'=>new GraduateAttributeResource($graduateAttribute),
                'message'=>'graduate attribute updated successfully'
            ],200);

        }catch(Exception $e){
            return response()->json([
                'message'=>$e instanceof ModelNotFoundException ? 'graduate attribute not found' :'failed to update graduate attribute',
                'error'=>$e->getMessage(),
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GraduateAttribute $graduateAttribute)
    {
        try{
            $graduateAttribute->delete();
            
            return response()->json([
                'message'=>'graduate attribute deleted successfully'
            ],200);
        }catch(Exception $e){

            return response()->json([
                'message'=> $e instanceof ModelNotFoundException ? 'graduate attribute not found' : 'failed to delete graduate attribute',
            ],500);
        }
    }
}