<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\CurriculumCourseRequest;
use App\Http\Resources\Api\V1\Department\CurriculumCourseResource;
use App\Models\CurriculumCourse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class CurriculumCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department,Faculty_Member,Dean');
    }

    /* public function index()
    {
        try {
            $user = auth()->user();
            $query = CurriculumCourse::with(['curriculum', 'course', 'semester', 'courseCategory']);

            // If user is Faculty_Member, only fetch courses assigned to them as committee member
            if ($user->role->name === 'Faculty_Member') {
                // Find committees for this user
                $committees = $user->committees;

                if ($committees->isEmpty()) {
                    return CurriculumCourseResource::collection(collect([]))->additional([
                        'message' => 'No curriculum courses assigned to you',
                    ]);
                }

                // Get curriculum courses assigned to this faculty member through committees
                $committeeIds = $committees->pluck('id')->toArray();

                $query->whereHas('committees', function ($q) use ($committeeIds) {
                    $q->whereIn('committees.id', $committeeIds)
                        ->where('committee_course_assignments.is_completed', false);
                });
            }

            $curriculumCourses = $query->get();

            return CurriculumCourseResource::collection($curriculumCourses)->additional([
                'message' => 'curriculum courses retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve curriculum courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }*/

    public function index(Request $request)
    {
        try {
            // Start with the base query
            $query = CurriculumCourse::with([
                'curriculum',
                'course',
                'courseCategory',
                'semester',
                'committees',
            ]);

            // Conditionally load course outcomes if requested
            if ($request->has('include_outcomes') && $request->input('include_outcomes') == 'true') {
                $query->with([
                    'cos',
                    'cos.abcd',
                    'cos.cpa',
                    'cos.pos',
                    'cos.tlaTasks',
                    'cos.tlaMethod'
                ]);
            }

            // Execute the query with pagination. save for the future
            // $curriculumCourses = $query->paginate($request->input('per_page', 15));
            $curriculumCourses = $query->get();

            return CurriculumCourseResource::collection($curriculumCourses)->additional([
                'message' => 'Curriculum courses retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve curriculum courses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CurriculumCourseRequest $request)
    {
        try {
            $newCurriculumCourse = DB::transaction(function () use ($request) {
                return CurriculumCourse::create($request->validated());
            });

            return (new CurriculumCourseResource($newCurriculumCourse))->additional([
                'message' => 'curriculum resource created successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to create curriculum courses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /* public function show(CurriculumCourse $curriculumCourse)
    {

        try {
            $curriculumCourse->load(['curriculum', 'course', 'courseCategory', 'semester']);

            return (new CurriculumCourseResource($curriculumCourse))->additional([
                'message' => 'Curriculum course retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }*/

    public function show(CurriculumCourse $curriculumCourse)
    {
        try {
            // Load all necessary relationships
            $curriculumCourse->load([
                'curriculum',
                'course',
                'courseCategory',
                'semester',
                'committees',
                'cos', // Load course outcomes
                'cos.abcd', // Load ABCD components
                'cos.cpa', // Load CPA information
                'cos.pos', // Load program outcome mappings
                'cos.tlaTasks', // Load TLA tasks
                'cos.tlaMethod' // Load teaching/learning methods
            ]);

            return (new CurriculumCourseResource($curriculumCourse))->additional([
                'message' => 'Curriculum course retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(CurriculumCourseRequest $request, CurriculumCourse $curriculumCourse)
    {
        try {
            $curriculumCourse->update($request->validated());

            return response()->json([
                'message' => 'Curriculum course updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to update curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CurriculumCourse $curriculumCourse)
    {
        //
        try {
            $curriculumCourse->delete();

            return response()->json([
                'message' => 'Curriculum course deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to delete curriculum course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}