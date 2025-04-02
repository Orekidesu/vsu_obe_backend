<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\CurriculumRequest;
use App\Http\Resources\Api\V1\Department\CurriculumResource;
use App\Models\Curriculum;
use Exception;
use Illuminate\Http\Request;

class CurriculumController extends Controller
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
            $curriculums = Curriculum::with('program');

            return CurriculumResource::collection($curriculums)->additional([
                'message' => 'curricula retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed tor retrieve curricula',
                'error' => $e->getMessage(),
            ]);
        }    //


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CurriculumRequest $request)
    {
        try {
            $newCurriculum = Curriculum::create($request->validated());

            return (new CurriculumResource($newCurriculum))->additional([
                'message' => 'curriculum created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create curriculum',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Curriculum $curriculum)
    {
        //
        try {
            $curriculum->load('program');

            return (new CurriculumResource($curriculum))->additional([
                'message' => 'curriculum retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve curriculum',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurriculumRequest $request, Curriculum $curriculum)
    {
        //
        try {
            $curriculum->update($request->validated());

            return (new CurriculumResource($curriculum))->additional([
                'message' => 'curriculum updated successfully',

            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update curriculum',
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Curriculum $curriculum)
    {

        try {
            $curriculum->delete();

            return response()->json([
                'message' => 'Curriculum deleted successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete curriculum',
                'error' => $e->getMessage(),
            ], 500);
        }   //
    }
}
