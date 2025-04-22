<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramProposalRequest;
use App\Http\Resources\Api\V1\Department\ProgramProposalResource;
use App\Models\ProgramProposal;
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

    // public function index(Request $request)
    // {
    //     try {
    //         // Get user for role-based filtering
    //         $user = auth()->user();

    //         // Start building the query
    //         $query = ProgramProposal::with([
    //             'program.department',
    //             'program.programEducationalObjectives:id,program_id,statement',
    //             'program.programOutcomes:id,program_id,name,statement',
    //             'program.curriculum:id,program_id,name'
    //         ]);

    //         // Filter by department if user is from Department role
    //         if ($user->role->name === 'Department') {
    //             $departmentId = $user->department_id;
    //             $query->whereHas('program', function ($q) use ($departmentId) {
    //                 $q->where('department_id', $departmentId);
    //             });
    //         }

    //         // Filter by status if provided
    //         if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected', 'revision'])) {
    //             $query->where('status', $request->status);
    //         }

    //         // Order by latest first
    //         $query->latest();

    //         // Paginate results if requested
    //         if ($request->has('per_page')) {
    //             $proposals = $query->paginate($request->per_page);
    //         } else {
    //             $proposals = $query->get();
    //         }

    //         // Return collection with additional metadata
    //         return ProgramProposalResource::collection($proposals)->additional([
    //             'message' => 'Program proposals retrieved successfully',
    //             'meta' => [
    //                 'total_pending' => ProgramProposal::where('status', 'pending')->count(),
    //                 'total_approved' => ProgramProposal::where('status', 'approved')->count(),
    //                 'total_rejected' => ProgramProposal::where('status', 'rejected')->count(),
    //                 'total_revision' => ProgramProposal::where('status', 'revision')->count(),
    //                 'department_counts' => $this->getDepartmentProposalCounts(),
    //             ],
    //         ]);
    //     } catch (Exception $e) {
    //         Log::error('Failed to retrieve program proposals', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'message' => 'Failed to retrieve program proposals',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function index(Request $request)
    {
        try {
            // Get user for role-based filtering
            $user = auth()->user();

            // Start building the query with all relationships needed for detailed view
            $query = ProgramProposal::with([
                'program.department',
                'program.programEducationalObjectives.missions',
                'program.programEducationalObjectives.gas',
                'program.programOutcomes.peos',
                'program.programOutcomes.gas',
                'program.curriculum.curriculumCourses.course',
                'program.curriculum.curriculumCourses.courseCategory',
                'program.curriculum.curriculumCourses.semester',
                'program.curriculum.curriculumCourses.pos',
            ]);

            // Filter by department if user is from Department role
            if ($user->role->name === 'Department') {
                $departmentId = $user->department_id;
                $query->whereHas('program', function ($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }

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
                // 'meta' => [
                //     'total_pending' => ProgramProposal::where('status', 'pending')->count(),
                //     'total_approved' => ProgramProposal::where('status', 'approved')->count(),
                //     'total_rejected' => ProgramProposal::where('status', 'rejected')->count(),
                //     'total_revision' => ProgramProposal::where('status', 'revision')->count(),
                //     'department_counts' => $this->getDepartmentProposalCounts(),
                // ],
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
                'program.programEducationalObjectives.missions',
                'program.programEducationalObjectives.gas',
                'program.programOutcomes.peos',
                'program.programOutcomes.gas',
                'program.curriculum.curriculumCourses.course',
                'program.curriculum.curriculumCourses.courseCategory',
                'program.curriculum.curriculumCourses.semester',
                'program.curriculum.curriculumCourses.pos',
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
        Log::info('Authenticated user role:', ['role' => auth()->user()->role->name]);

        $request->validate([
            'status' => 'required|in:approved,rejected,revision',
            'comment' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $programProposal) {
                // Check if the program has already been reviewed
                if ($programProposal->status !== 'pending') {
                    throw new Exception('This proposal has already been reviewed');
                }

                // Update the proposal's status and comment
                $programProposal->update([
                    'status' => $request->status,
                    'comment' => $request->comment,
                ]);

                // If approved, update the associated program status and archive
                if ($request->status === 'approved') {
                    $program = $programProposal->program;
                    // Archive the currently active program (if there's any)
                    Program::where('name', $program->name)
                        ->where('abbreviation', $program->abbreviation)
                        ->where('status', 'active') // Find the currently active program
                        ->update(['status' => 'archived']);

                    // Update the newly approved program proposal to be the new active program
                    $programProposal->program()->update(['status' => 'active']);
                }
            });

            return response()->json([
                'message' => 'Proposal reviewed successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to review proposal',
                'error' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Update the specified resource in storage.
     */
}
