<?php

namespace App\Http\Resources\Api\V1\Department;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramProposalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $this->load([
            'peos.missions',
            'peos.gas',
            'pos.peos',
            'pos.gas',
            'curriculum.curriculumCourses.course',
            'curriculum.curriculumCourses.courseCategory',
            'curriculum.curriculumCourses.semester',
            'curriculum.curriculumCourses.pos',
            'committees.user',
            'committees.curriculumCourses.course',
        ]);
        $program = $this->program;

        // Get Peos with relationships
        $peos = $this->peos;

        // Get POs with relationsiops

        $pos = $this->pos;

        // getcurriculum with courses
        $curriculum = $this->curriculum;

        // Get Committees
        $committees = $this->committees;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'comment' => $this->comment,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Add the proposer (department user)
            'proposed_by' => $this->whenLoaded('proposedBy', function () {
                return [
                    'id' => $this->proposedBy->id,
                    'first_name' => $this->proposedBy->first_name,
                    'last_name' => $this->proposedBy->last_name,
                    'email' => $this->proposedBy->email,
                ];
            }),

            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'abbreviation' => $program->abbreviation,
                'department_id' => $program->department_id,
                'department_name' => $program->department->name,
                'department_abbreviation' => $program->department->abbreviation,
                'version' => $program->version,
                'status' => $program->status,
            ],
            'peos' => $peos->map(function ($peo, $index) {
                return [
                    'id' => $peo->id,
                    'statement' => $peo->statement,
                    'missions' => $peo->missions->map(function ($mission) {
                        return [
                            'id' => $mission->id,
                            'mission_no' => $mission->mission_no,
                            'description' => $mission->description,
                        ];
                    }),
                    'graduate_attributes' => $peo->gas ? $peo->gas->map(function ($ga) {
                        return [
                            'id' => $ga->id,
                            'ga_no' => $ga->ga_no,
                            'name' => $ga->name,
                        ];
                    }) : [],
                ];
            }),
            'pos' => $pos->map(function ($po, $index) {
                return [
                    'id' => $po->id,
                    'name' => $po->name,
                    'statement' => $po->statement,
                    'peos' => $po->peos ? $po->peos->map(function ($peo) {
                        return [
                            'id' => $peo->id,
                            'statement' => $peo->statement,
                        ];
                    }) : [],
                    'graduate_attributes' => $po->gas ? $po->gas->map(function ($ga) {
                        return [
                            'id' => $ga->id,
                            'ga_no' => $ga->ga_no,
                            'name' => $ga->name,
                        ];
                    }) : [],
                ];
            }),
            'curriculum' => $curriculum ? [
                'id' => $curriculum->id,
                'name' => $curriculum->name,
                'courses' => $curriculum->curriculumCourses->map(function ($cc) {
                    return [
                        'id' => $cc->id,
                        'course' => [
                            'id' => $cc->course->id,
                            'code' => $cc->course->code,
                            'descriptive_title' => $cc->course->descriptive_title,
                        ],
                        'category' => [
                            'id' => $cc->courseCategory->id,
                            'name' => $cc->courseCategory->name,
                            'code' => $cc->courseCategory->code,
                        ],
                        'semester' => [
                            'id' => $cc->semester->id,
                            'year' => $cc->semester->year,
                            'sem' => $cc->semester->sem,
                        ],
                        'units' => $cc->unit,
                        'po_mappings' => $cc->pos ? $cc->pos->map(function ($po) use ($cc) {
                            $pivot = $po->pivot;
                            return [
                                'po_id' => $po->id,
                                'po_name' => $po->name,
                                'ied' => json_decode($pivot->ied),
                            ];
                        }) : [],
                    ];
                }),
            ] : null,
            'committees' => $committees ? $committees->map(function ($committee) {
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
                            'is_completed' => $cc->pivot->is_completed ?? false,
                            'is_in_revision' => $cc->pivot->is_in_revision ?? false,

                        ];
                    }),
                ];
            }) : [],


        ];
    }
}
