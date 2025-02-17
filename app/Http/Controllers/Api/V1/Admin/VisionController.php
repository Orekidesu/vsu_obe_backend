<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\VisionRequest;
use App\Http\Resources\Api\V1\Admin\VisionResource;
use Illuminate\Support\Facades\Auth;
use App\Models\Vision;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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
        try{
            $visions = Vision::all();

            return response()->json([
                'data'=>VisionResource::collection($visions),
                'message'=>"vision retrieved successfully",
            ],200);

        }catch(Exception $e)
        {
          $visions = Vision::all();

          return response()->json([
            'message'=>'Failed to retrieve visions',
            'error'=>$e->getMessage(),
          ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VisionRequest $request)
    {
        
        try{
            $vision = Vision::create($request->validated());

            return response()->json([
                'data' => new VisionResource($vision),
                'message'=> 'vision successfully created',
            ],201);

        }catch(Exception $e){

            return response()->json([
                'message'=>'Failed to create vision',
                'error' => $e->getMessage(),
            ],500);

        }
       
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try{
            $vision = Vision::findOrFail($id);
            
            return response([
                'data' => new VisionResource($vision),
                'message' =>'vision retrieved successfully',
            ],200);
            
        }catch(ModelNotFoundException $e){

            return response()->json([
                'message'=> ' Vision not found',
                'error' => $e->getMessage(),
            ]);
                
        }catch(Exception $e){

            return response()->json([
                'message' => 'Failed to retrive vision',
                'error' =>$e->getMessage(),
            ]);

        }   
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VisionRequest $request, $id)
    {
        try{
            $vision = Vision::findOrFail($id);
            $vision->update($request->validated());

            return response([
                'data' => new VisionResource($vision),
                'message'=> 'vision updated successfully'
            ],200);
        }catch(ModelNotFoundException $e){
            return response()->json([
                'message' => 'vision not found',
                'error' => $e->getMessage(),
            ]);

        }catch(Exception $e){
            return response()->json([
                'message'=> 'failed to update vision',
                'error'=> $e->getMessage(),
            ]);
        }

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
       try{
            $vision = Vision::findOrFail($id);
            $vision->delete();

            return response([
                'message' => 'vision deleted successfully'
            ],200);

       }catch(ModelNotFoundException $e){

            return response()->json([
                'message'=>'vision not found',
                'error' => $e->getMessage(),
            ]);
    

       }catch(Exception $e){

        return response()->json([
            'message'=> 'failed to delete vision',
            'error'=> $e->getMessage(),
        ]);

       }
    }
}
