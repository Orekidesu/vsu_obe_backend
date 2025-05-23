<?php

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Department\ProgramOutcomeResource;
use App\Models\CurriculumCourse;

class CurriculumCoursePOController extends Controller
{
    //

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department,Faculty_Member');
    }

    public function getProgramOutcomes(CurriculumCourse $curriculumCourse)
    {
        $curriculumCourse->load(['pos' => function ($query) {
            $query->orderBy('name');
        }]);

        $programOutcomes = $curriculumCourse->pos;


        if ($programOutcomes->isEmpty()) {
            return response()->json([
                'message' => 'No pos found for this curriculum course',
                'data' => [],
            ]);
        }

        return ProgramOutcomeResource::collection($programOutcomes)->additional([
            'message' => 'POs retrieved successfully',
        ]);
    }
}
