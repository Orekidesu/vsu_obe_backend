<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;

use App\Models\ProgramProposalRevision;
use App\Models\ProgramProposal;
use Exception;

class FetchDepartmentRevisionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department,Dean');
    }

    public function fetchRevisions(ProgramProposal $programProposal)
    {
        //  Ensure the user belongs to the department of the proposal
        if (auth()->user()->department_id !== $programProposal->program->department_id) {
            return response()->json([
                'message' => 'You are not authorized to view this proposal.'
            ], 403);
        }

        try {
            $latestVersion = ProgramProposalRevision::where('program_proposal_id', $programProposal->id)
                ->max('version');
            // Fetch all department-level revisions
            $revisions = ProgramProposalRevision::where('program_proposal_id', $programProposal->id)
                ->where('level', 'department')
                ->where('version', $latestVersion)
                ->get(['id', 'section', 'details', 'created_at', 'version']);

            return response()->json([
                'program_proposal_id' => $programProposal->id,
                'version' => $latestVersion,
                'revisions' => $revisions,
                'message' => 'Department-level revisions fetched successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'failed to retrieve department revisions from this proposal',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
