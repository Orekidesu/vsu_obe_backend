<?php

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Controller;
use App\Models\ProgramProposal;
use App\Models\ProgramProposalRevision;
use App\Models\CurriculumCourse;
use Exception;

class FetchBothLevelRevisionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('role:Dean');
        $this->middleware('auth:sanctum');
    }

    public function fetchRevisions(ProgramProposal $programProposal)
    {
        try {
            // Get the latest version number for this proposal
            $latestVersion = ProgramProposalRevision::where('program_proposal_id', $programProposal->id)
                ->max('version');

            if (!$latestVersion) {
                return response()->json([
                    'message' => 'No revisions found for this proposal.'
                ], 404);
            }

            // Fetch all department-level revisions at the latest version
            $departmentRevisions = ProgramProposalRevision::where('program_proposal_id', $programProposal->id)
                ->where('level', 'department')
                ->where('version', $latestVersion)
                ->get(['id', 'section', 'details', 'created_at', 'version']);

            // Fetch all committee-level revisions at the latest version
            $committeeRevisions = ProgramProposalRevision::where('program_proposal_id', $programProposal->id)
                ->where('level', 'committee')
                ->where('version', $latestVersion)
                ->with(['curriculumCourse' => function ($query) {
                    $query->with('course:id,code,descriptive_title');
                }])
                ->get(['id', 'curriculum_course_id', 'section', 'details', 'created_at', 'version']);

            // Group committee revisions by curriculum course for better organization
            $groupedCommitteeRevisions = $committeeRevisions->groupBy('curriculum_course_id')
                ->map(function ($revisions) {
                    $curriculumCourse = $revisions->first()->curriculumCourse;
                    $course = $curriculumCourse->course;

                    return [
                        'curriculum_course_id' => $curriculumCourse->id,
                        'course_code' => $course ? $course->code : 'N/A',
                        'course_title' => $course ? $course->descriptive_title : 'N/A',
                        'revisions' => $revisions->map(function ($revision) {
                            return [
                                'id' => $revision->id,
                                'section' => $revision->section,
                                'details' => $revision->details,
                                'created_at' => $revision->created_at
                            ];
                        })
                    ];
                })->values();

            return response()->json([
                'program_proposal_id' => $programProposal->id,
                'version' => $latestVersion,
                'department_revisions' => $departmentRevisions,
                'committee_revisions' => $groupedCommitteeRevisions,
                'message' => 'All revisions fetched successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve revisions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}