<?php

namespace App\Http\Controllers\Api\V1\Dean;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProgramProposal;
use App\Models\ProgramProposalRevision;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class ProposalReviewController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Dean');
    }

    //
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
}