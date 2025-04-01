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


class ProgramProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department')->except(['index', 'show']);
        $this->middleware('role:Dean')->only(['review']);
    }
    public function index()
    {
        //
        try {
            $proposals = ProgramProposal::with('program')->get();

            return ProgramProposalResource::collection($proposals)->additional([
                'message' => 'program proposals retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'messsage' => 'failed to retrieve program proposals',
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
            $program = Program::findOrFail($request->program_id);

            // check first if there is an existing pending proposal,

            $existingPendingProposal = ProgramProposal::where('program_id', $request->program_id)
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
            $programProposal->load('program');

            return (new ProgramProposalResource($programProposal))->additional([
                'message' => 'program proposal retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve program proposal',
                'error' => $e->getMessage(),
            ], 500);
        }
        //
    }

    public function review(Request $request, ProgramProposal $programProposal)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,revision',
            'comment' => 'nullable|string',

        ]);
        DB::transaction();
        try {
            // check if program already beend reviewed
            if ($programProposal->status !== 'pending') {
                return response()->json([
                    'message' => 'This proposal has already been reviewed',
                ], 400);
            }

            $programProposal->update([
                'status' => $request->status,
                'comment' => $request->comment,
            ]);

            // if Approved, update the associated program status, and archive
            if ($request->status === 'approved') {
                // archive the currently active program (if there's any)
                Program::where('id', $programProposal->program_id)
                    ->where('status', 'active') //find the currently active program
                    ->update(['status' => 'archived']);

                // update the newly approved program proposal to be the new active program
                $programProposal->program()->update(['status' => 'approved']);
            }


            // udpate the proposal's status
            $programProposal->update([
                'status' => $request->status,
                'comment' => $request->comment,
            ]);
            DB::commit();

            return response()->json([
                'message' => 'Proposal reviewed successfully',
            ]);
        } catch (Exception $e) {
            DB::rollBack();
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