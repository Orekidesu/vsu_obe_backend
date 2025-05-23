<?php

namespace App\Http\Controllers\Api\V1\Faculty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CurriculumCourse;
use App\Models\Committee;
use App\Models\ProgramProposalRevision;
use Exception;

class FetchCommitteeRevisionController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Faculty_Member,Dean');
    }

    public function fetchRevisions(CurriculumCourse $curriculumCourse)
    {
        try {
            // Verify the user is part of the committee assigned to the course
            $programProposal = $curriculumCourse->curriculum->programProposal;

            if (!$programProposal) {
                return response()->json([
                    'message' => 'No program proposal found for this curriculum course.'
                ], 404);
            }

            // Get the latest version number
            $latestVersion = ProgramProposalRevision::where('program_proposal_id', $programProposal->id)
                ->max('version');

            // Check if faculty member is assigned to this curriculum course committee
            $committee = Committee::where('user_id', auth()->user()->id)
                ->whereHas('curriculumCourses', function ($q) use ($curriculumCourse) {
                    $q->where('curriculum_course_id', $curriculumCourse->id);
                })->exists();

            if (!$committee) {
                return response()->json([
                    'message' => 'You are not authorized to view this curriculum course.'
                ], 403);
            }

            // Fetch ONLY the latest version of committee-level revisions for that course
            $revisions = ProgramProposalRevision::where('curriculum_course_id', $curriculumCourse->id)
                ->where('level', 'committee')
                ->where('version', $latestVersion) // This ensures we get only the latest version
                ->get(['id', 'section', 'details', 'created_at', 'version']);

            return response()->json([
                'curriculum_course_id' => $curriculumCourse->id,
                'version' => $latestVersion,
                'revisions' => $revisions,
                'message' => 'Latest committee-level revisions fetched successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve revision data for this curriculum course',
                'error' => $e->getMessage()
            ], 500); // Added proper 500 status code for server errors
        }
    }
}
