<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Department\ProgramRequest;
use App\Http\Resources\Api\V1\Department\ProgramProposalResource;
use App\Http\Resources\Api\V1\Department\ProgramResource;
use App\Models\Program;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\ProgramProposal;

class ProgramController extends Controller
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
            // Get user for role-based filtering
            $user = auth()->user();

            // Start building the query with the department relationship
            $query = Program::with('department');

            // Filter by department if user is from Department role
            if ($user->role->name === 'Department') {
                $departmentId = $user->department_id;
                $query->where('department_id', $departmentId);
            }

            // Filter by status if provided
            if (request()->has('status') && in_array(request()->status, ['active', 'pending', 'archived'])) {
                $query->where('status', request()->status);
            }

            // First get programs without loading heavy relationships yet
            $programs = $query->latest()->get();

            // Now load relevant proposals for each program based on program status
            $programs->load([
                'proposal' => function ($query) {
                    $query->latest(); // Always get the latest proposals first
                },
                'proposal.peos.missions',
                'proposal.peos.gas',
                'proposal.pos.peos',
                'proposal.pos.gas',
                'proposal.curriculum.curriculumCourses.course',
                'proposal.curriculum.curriculumCourses.courseCategory',
                'proposal.curriculum.curriculumCourses.semester',
                'proposal.curriculum.curriculumCourses.pos',
            ]);

            // Get counts for meta information
            $counts = [
                'total_pending' => Program::where('status', 'pending'),
                'total_active' => Program::where('status', 'active'),
                'total_archived' => Program::where('status', 'archived'),
            ];

            // Apply department filter to counts if needed
            if ($user->role->name === 'Department') {
                foreach ($counts as &$countQuery) {
                    $countQuery->where('department_id', $user->department_id);
                }
            }

            // Use proper pagination if needed
            if (request()->has('per_page')) {
                // Since we've already loaded the data, let's do manual pagination
                $perPage = (int)request()->per_page;
                $currentPage = (int)request()->input('page', 1);
                $offset = ($currentPage - 1) * $perPage;

                $paginatedPrograms = $programs->slice($offset, $perPage);
                $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
                    $paginatedPrograms,
                    $programs->count(),
                    $perPage,
                    $currentPage,
                    ['path' => request()->url()]
                );

                $programsCollection = $paginator;
            } else {
                $programsCollection = $programs;
            }

            return ProgramResource::collection($programsCollection)->additional([
                'message' => 'Programs retrieved successfully',
                'meta' => [
                    'total_pending' => $counts['total_pending']->count(),
                    'total_active' => $counts['total_active']->count(),
                    'total_archived' => $counts['total_archived']->count(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve programs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramRequest $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $existingPending = Program::where('name', $validated['name'])
                ->where('abbreviation', $validated['abbreviation'])
                ->where('status', 'pending')
                ->exists();

            if ($existingPending) {
                DB::rollBack(); // Close the transaction before returning
                return response()->json([
                    'message' => 'A pending version of program already exists',
                ], 409);
            }

            // Latest version of program
            $latestVersion = Program::where('name', $validated['name'])
                ->where('abbreviation', $validated['abbreviation'])
                ->max('version');

            // Add 1 if a previous program exists, set value to 1 if none
            $newVersion = $latestVersion ? $latestVersion + 1 : 1;

            $data = array_merge($validated, [
                'version' => $newVersion,
                'status' => 'pending'
            ]);

            $program = Program::create($data);
            //in this case, the department isnt included in the response because it is newly created 
            // thus, eager loading isnt applied yet
            // so, to include the department in the response, we need to explicitly load the department
            // only do this, if it is really necessary to include in the response
            $program->load('department');

            $newProposal = ProgramProposal::create([
                'program_id' => $program->id,
                'abbreviation' => $program->abbreviation,
                'version' => $program->version,
                'status' => 'pending',
                'comment' => null,
            ]);

            DB::commit();

            return response()->json([
                'data' => [
                    'program' => new ProgramResource($program),
                    'proposal' => new ProgramProposalResource($newProposal),
                ],
                'message' => 'Program created and submitted for approval successfully'
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create program',
                'error' => $e->getMessage(),
            ], 500); // Add appropriate status code
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        try {
            // Load all necessary relationships at the controller level
            $program->load([
                'department',
                'proposal' => function ($query) use ($program) {
                    if ($program->status === 'pending') {
                        $query->where('status', 'pending');
                    } else {
                        $query->where('status', 'approved');
                    }
                    $query->latest();
                },
                'proposal.peos.missions',
                'proposal.peos.gas',
                'proposal.pos.peos',
                'proposal.pos.gas',
                'proposal.curriculum.curriculumCourses.course',
                'proposal.curriculum.curriculumCourses.courseCategory',
                'proposal.curriculum.curriculumCourses.semester',
                'proposal.curriculum.curriculumCourses.pos',
            ]);

            return (new ProgramResource($program))->additional([
                'message' => 'Program retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve program',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProgramRequest $request, Program $program)
    {
        //
        try {
            $program->update($request->validated());

            return (new ProgramResource($program))->additional([
                'message' => 'program updated successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to update program',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        //
        try {
            $program->delete();

            return response()->json([
                'message' => 'program deleted successfully'
            ]);
        } catch (Exception $e) {
        }
    }
}