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
        $program = $this->program;

        $program->load([
            'programEducationalObjectives.missions',
            'programEducationalObjectives.gas',
            'programOutcomes.peos',
            'programOutcomes.gas',
            'curriculum.curriculumCourses.course',
            'curriculum.curriculumCourses.courseCategory',
            'curriculum.curriculumCourses.semester',
            'curriculum.curriculumCourses.pos',
        ]);

        // Get Peos with relationships
        $peos = $program->programEducationalObjectives;

        // Get POs with relationsiops

        $pos = $program->programOutcomes;

        // getcurriculum with courses

        $curriculum = $program->curriculum;

        return [
            'id' => $this->id,
            'status' => $this->status,
            'comment' => $this->comment,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'program' => [
                'id' => $program->id,
                'name' => $program->name,
                'abbreviation' => $program->abbreviation,
                'department_id' => $program->department_id,
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
                                'ird' => json_decode($pivot->ird),
                            ];
                        }) : [],
                    ];
                }),
            ] : null,

        ];
    }
}
