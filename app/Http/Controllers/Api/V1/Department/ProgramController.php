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
            // eager load using with(relationship) function
            // since we eager loaded it in the model already, no need for manual with() function

            $programs = Program::all();

            return ProgramResource::collection($programs)->additional([
                'message' => 'Programs retrieved successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve programs',
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
        //
        try {
            return (new ProgramResource($program))->additional([
                'message' => 'Program retrieved successfully'
            ]);
        } catch (Exception $e) {

            return response()->json(
                [
                    'message' => 'failed to retrieve program',
                    'error' => $e->getMessage(),
                ]
            );
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