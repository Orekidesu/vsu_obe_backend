<?php

namespace App\Http\Controllers\Api\V1\Department;

use App\Http\Controllers\Controller;
use App\Models\ProgramProposal;
use Exception;
use Illuminate\Http\Request;

class CommittteeCourseAssignmentController extends Controller
{
    //
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:Department');
    }

    public function getAllCurriculumCommittees(ProgramProposal $programProposal)
    {
        try {
            // Ensure the user belongs to the department of the proposal
            if (auth()->user()->department_id !== $programProposal->program->department_id) {
                return response()->json([
                    'message' => 'You are not authorized to view this proposal.'
                ], 403);
            }

            // Load committees with their relationships
            $programProposal->load([
                'committees.user',
                'committees.assignedBy',
                'committees.curriculumCourses.course',
                'committees.curriculumCourses.courseCategory',
                'committees.curriculumCourses.semester'
            ]);

            $committees = $programProposal->committees->map(function ($committee) {
                return [
                    'id' => $committee->id,
                    'user' => [
                        'id' => $committee->user->id,
                        'first_name' => $committee->user->first_name,
                        'last_name' => $committee->user->last_name,
                        'email' => $committee->user->email,
                    ],
                    'assigned_by' => [
                        'id' => $committee->assignedBy->id,
                        'first_name' => $committee->assignedBy->first_name,
                        'last_name' => $committee->assignedBy->last_name,
                    ],
                    'assigned_courses' => $committee->curriculumCourses->map(function ($cc) {
                        return [
                            'curriculum_course_id' => $cc->id,
                            'course_code' => $cc->course->code,
                            'descriptive_title' => $cc->course->descriptive_title,
                            'category' => $cc->courseCategory->name,
                            'semester' => $cc->semester->year . ' - ' . $cc->semester->sem,
                            'units' => $cc->unit,
                            'is_completed' => $cc->pivot->is_completed ?? false,
                            'is_in_revision' => $cc->pivot->is_in_revision ?? false,
                        ];
                    }),
                    'created_at' => $committee->created_at,
                    'updated_at' => $committee->updated_at,
                ];
            });

            return response()->json([
                'program_proposal_id' => $programProposal->id,
                'program_name' => $programProposal->program->name,
                'committees' => $committees,
                'total_committees' => $committees->count(),
                'message' => 'Committees retrieved successfully'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch all committees for this curriculum',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function assignCourseToCommittee(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'curriculum_course_id' => 'required|exists:curriculum_courses,id',
                'committee_id' => 'required|exists:committees,id'
            ]);

            $curriculumCourseId = $request->curriculum_course_id;
            $committeeId = $request->committee_id;

            // Get the curriculum course and committee
            $curriculumCourse = \App\Models\CurriculumCourse::with('curriculum.programProposal.program')->findOrFail($curriculumCourseId);
            $committee = \App\Models\Committee::with('programProposal.program')->findOrFail($committeeId);

            // Ensure both belong to the same program proposal
            if ($curriculumCourse->curriculum->programProposal->id !== $committee->program_proposal_id) {
                return response()->json([
                    'message' => 'The curriculum course and committee must belong to the same program proposal.'
                ], 400);
            }

            // Ensure the user belongs to the department
            if (auth()->user()->department_id !== $committee->programProposal->program->department_id) {
                return response()->json([
                    'message' => 'You are not authorized to assign courses for this program.'
                ], 403);
            }

            // Check if the assignment already exists
            $existingAssignment = $committee->curriculumCourses()
                ->where('curriculum_course_id', $curriculumCourseId)
                ->exists();

            if ($existingAssignment) {
                return response()->json([
                    'message' => 'This curriculum course is already assigned to the committee.'
                ], 409);
            }

            // Check if the curriculum course is assigned to another committee
            $otherCommitteeAssignment = \App\Models\Committee::whereHas('curriculumCourses', function ($query) use ($curriculumCourseId) {
                $query->where('curriculum_course_id', $curriculumCourseId);
            })->where('id', '!=', $committeeId)->exists();

            if ($otherCommitteeAssignment) {
                return response()->json([
                    'message' => 'This curriculum course is already assigned to another committee. Please remove the existing assignment first.'
                ], 409);
            }

            // Create the assignment
            $committee->curriculumCourses()->attach($curriculumCourseId, [
                'is_completed' => false,
                'is_in_revision' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Load the updated data for response
            $committee->load(['user', 'curriculumCourses.course', 'curriculumCourses.courseCategory', 'curriculumCourses.semester']);

            $assignedCourse = $committee->curriculumCourses()->where('curriculum_course_id', $curriculumCourseId)->first();

            return response()->json([
                'message' => 'Curriculum course assigned to committee successfully.',
                'assignment' => [
                    'committee' => [
                        'id' => $committee->id,
                        'user' => [
                            'id' => $committee->user->id,
                            'first_name' => $committee->user->first_name,
                            'last_name' => $committee->user->last_name,
                            'email' => $committee->user->email,
                        ]
                    ],
                    'curriculum_course' => [
                        'id' => $assignedCourse->id,
                        'course_code' => $assignedCourse->course->code,
                        'descriptive_title' => $assignedCourse->course->descriptive_title,
                        'category' => $assignedCourse->courseCategory->name,
                        'semester' => $assignedCourse->semester->year . ' - ' . $assignedCourse->semester->sem,
                        'units' => $assignedCourse->unit,
                        'is_completed' => false,
                        'is_in_revision' => false,
                    ]
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to assign curriculum course to committee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
