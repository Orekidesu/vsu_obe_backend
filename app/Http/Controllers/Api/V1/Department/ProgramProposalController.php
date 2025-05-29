<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramProposalRequest;
use App\Http\Resources\Api\V1\Department\ProgramProposalResource;
use App\Models\ProgramProposal;
use App\Models\ProgramProposalRevision;
use App\Models\Program;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ProgramProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department')->except(['index', 'show', 'review']);
        $this->middleware('role:Dean')->only(['review']);
    }



    public function index(Request $request)
    {
        try {
            // Get user for role-based filtering
            $user = auth()->user();

            // Start building the query with all relationships needed for detailed view
            $query = ProgramProposal::with([
                'proposedBy',
                'program.department',
                'peos.missions',
                'peos.gas',
                'pos.peos',
                'pos.gas',
                'curriculum.curriculumCourses.course',
                'curriculum.curriculumCourses.courseCategory',
                'curriculum.curriculumCourses.semester',
                'curriculum.curriculumCourses.pos',

            ]);

            // Filter by department if user is from Department role
            if ($user->role->name === 'Department') {
                $departmentId = $user->department_id;
                $query->whereHas('program', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            } elseif ($user->role->name === 'Dean' && $user->faculty_id) {
                $departmentIds = \App\Models\Department::where('faculty_id', $user->faculty_id)
                    ->pluck('id')
                    ->toArray();

                if (!empty($departmentIds)) {
                    $query->whereHas('program', function ($q) use ($departmentIds) {
                        $q->whereIn('department_id', $departmentIds);
                    });
                }
            }

            $query->whereIn('id', function ($subquery) {
                $subquery->select(DB::raw('MAX(id)'))
                    ->from('program_proposals')
                    ->groupBy('program_id');
            });

            // Filter by status if provided
            if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected', 'revision'])) {
                $query->where('status', $request->status);
            }

            // Order by latest first
            $query->latest();

            // Paginate results if requested
            if ($request->has('per_page')) {
                $proposals = $query->paginate($request->per_page);
            } else {
                $proposals = $query->get();
            }

            // Return collection with additional metadata
            return ProgramProposalResource::collection($proposals)->additional([
                'message' => 'Program proposals retrieved successfully',

            ]);
        } catch (Exception $e) {
            Log::error('Failed to retrieve program proposals', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve program proposals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramProposalRequest $request)
    {
        DB::beginTransaction();
        try {
            // Ensure the referenced program exist
            $validated = $request->validated();
            $program = Program::findOrFail($validated['program_id']);

            // check first if there is an existing pending proposal,

            $existingPendingProposal = ProgramProposal::where('program_id', $validated['program_id'])
                ->where('status', 'pending')
                ->exists();

            if ($existingPendingProposal) {
                return response()->json([
                    'message' => 'A pending proposal for this program has already existed',
                ], 409);
            }

            $newProposal = ProgramProposal::create([
                'program_id' => $program->id,
                'abbreviation' => $program->abbreviation,
                'version' => $program->version,
                'status' => 'pending',
                // 'comment' => $request->comment,
                'comment' => null,
            ]);

            DB::commit();

            return (new ProgramProposalResource($newProposal))->additional([
                'message' => 'Program Proposal Created Successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'failed to create program proposal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProgramProposal $programProposal)
    {
        try {
            // Load all necessary relationships
            $programProposal->load([
                'proposedBy',
                'program.department',
                'peos.missions',
                'peos.gas',
                'pos.peos',
                'pos.gas',
                'curriculum.curriculumCourses.course',
                'curriculum.curriculumCourses.courseCategory',
                'curriculum.curriculumCourses.semester',
                'curriculum.curriculumCourses.pos',
            ]);

            return (new ProgramProposalResource($programProposal))->additional([
                'message' => 'program proposal retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve program proposal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get proposal counts grouped by department
     * 
     * @return array
     */
    private function getDepartmentProposalCounts()
    {
        // Get counts of proposals grouped by department
        return DB::table('program_proposals as pp')
            ->join('programs as p', 'pp.program_id', '=', 'p.id')
            ->join('departments as d', 'p.department_id', '=', 'd.id')
            ->select(
                'd.name as department_name',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN pp.status = "pending" THEN 1 ELSE 0 END) as pending'),
                DB::raw('SUM(CASE WHEN pp.status = "approved" THEN 1 ELSE 0 END) as approved'),
                DB::raw('SUM(CASE WHEN pp.status = "rejected" THEN 1 ELSE 0 END) as rejected'),
                DB::raw('SUM(CASE WHEN pp.status = "revision" THEN 1 ELSE 0 END) as revision')
            )
            ->groupBy('d.name')
            ->get()
            ->toArray();
    }




    public function review(Request $request, ProgramProposal $programProposal)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,revision',
            'department_level' => 'array',
            'department_level.*.section' => 'required_with:department_level|string',
            'department_level.*.details' => 'required_with:department_level|string',
            'committee_level' => 'array',
            'committee_level.*.curriculum_course_id' => 'required_with:committee_level|exists:curriculum_courses,id',
            'committee_level.*.section' => 'required_with:committee_level|string|in:course_outcomes,co_po_mappings,co_abcd',
            'committee_level.*.details' => 'required_with:committee_level|string',
            'comment' => 'nullable|string'
        ]);

        if ($programProposal->status !== 'pending') {
            return response()->json(['message' => 'This proposal has already been reviewed'], 400);
        }

        DB::beginTransaction();
        try {
            $programProposal->update([
                'status' => $request->status,
                'comment' => $request->comment
            ]);

            // Insert revision entries
            if ($request->status === 'revision') {
                // Clear old revisions if needed (optional)
                // ProgramProposalRevision::where('program_proposal_id', $programProposal->id)->delete();

                foreach ($request->input('department_level', []) as $item) {
                    ProgramProposalRevision::create([
                        'program_proposal_id' => $programProposal->id,
                        'level' => 'department',
                        'section' => $item['section'],
                        'details' => $item['details']
                    ]);
                }

                foreach ($request->input('committee_level', []) as $item) {
                    ProgramProposalRevision::create([
                        'program_proposal_id' => $programProposal->id,
                        'level' => 'committee',
                        'curriculum_course_id' => $item['curriculum_course_id'],
                        'section' => $item['section'],
                        'details' => $item['details']
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Proposal reviewed successfully']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to review proposal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkReadyForReview(ProgramProposal $programProposal)
    {
        try {
            // Load the proposal with all necessary relationships
            $programProposal->load([
                'committees.curriculumCourses' => function ($query) {
                    // Eager load pivot data that contains is_completed status
                    $query->withPivot('is_completed');
                }
            ]);

            // Get all committees
            $committees = $programProposal->committees;

            // If no committees, return error
            if ($committees->isEmpty()) {
                return response()->json([
                    'message' => 'No committees assigned to this proposal',
                    'status' => 'error'
                ], 400);
            }

            // Check if all courses for all committees are completed
            $allCoursesCompleted = true;
            $totalAssignedCourses = 0;
            $completedCourses = 0;

            foreach ($committees as $committee) {
                $assignedCourses = $committee->curriculumCourses;

                // Count courses
                $totalAssignedCourses += $assignedCourses->count();
                $completedCourses += $assignedCourses->where('pivot.is_completed', true)->count();

                // Check if this committee has any incomplete courses
                if ($assignedCourses->where('pivot.is_completed', false)->count() > 0) {
                    $allCoursesCompleted = false;
                }
            }

            // Update proposal status if all courses are completed
            if ($allCoursesCompleted && $totalAssignedCourses > 0) {
                // Only update if current status is 'pending' or 'revision
                if ($programProposal->status === 'pending' || $programProposal->status === 'revision') {
                    $programProposal->update([
                        'status' => 'review'
                    ]);

                    return response()->json([
                        'message' => 'Program proposal is now ready for review',

                    ]);
                } else {
                    return response()->json([
                        'message' => 'All courses are completed, but proposal status cannot be updated because it is not in pending or revision state',

                    ]);
                }
            } else {
                return response()->json([
                    'message' => 'Not all assigned courses are completed',
                    'status' => 'incomplete',
                    'completed_courses' => $completedCourses,
                    'total_courses' => $totalAssignedCourses,
                    'completion_percentage' => $totalAssignedCourses > 0 ? round(($completedCourses / $totalAssignedCourses) * 100) : 0
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to check proposal status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}