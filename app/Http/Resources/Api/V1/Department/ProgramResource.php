<?php

namespace App\Http\Resources\Api\V1\Department;

use App\Http\Resources\Api\V1\Admin\DepartmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Load all the necessary relationships if they haven't been loaded yet
        if (!$this->relationLoaded('programEducationalObjectives')) {
            $this->load([
                'programEducationalObjectives.missions',
                'programEducationalObjectives.gas',
                'programOutcomes.peos',
                'programOutcomes.gas',
                'curriculum.curriculumCourses.course',
                'curriculum.curriculumCourses.courseCategory',
                'curriculum.curriculumCourses.semester',
                'curriculum.curriculumCourses.pos',
            ]);
        }

        // Get Peos with relationships
        $peos = $this->programEducationalObjectives;

        // Get POs with relationships
        $pos = $this->programOutcomes;

        // Get curriculum with courses
        $curriculum = $this->curriculum;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'status' => $this->status,
            'version' => $this->version,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'peos' => $peos ? $peos->map(function ($peo) {
                return [
                    'id' => $peo->id,
                    'statement' => $peo->statement,
                    'missions' => $peo->missions ? $peo->missions->map(function ($mission) {
                        return [
                            'id' => $mission->id,
                            'mission_no' => $mission->mission_no,
                            'description' => $mission->description,
                        ];
                    }) : [],
                    'graduate_attributes' => $peo->gas ? $peo->gas->map(function ($ga) {
                        return [
                            'id' => $ga->id,
                            'ga_no' => $ga->ga_no,
                            'name' => $ga->name,
                        ];
                    }) : [],
                ];
            }) : [],
            'pos' => $pos ? $pos->map(function ($po) {
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
            }) : [],
            'curriculum' => $curriculum ? [
                'id' => $curriculum->id,
                'name' => $curriculum->name,
                'courses' => $curriculum->curriculumCourses ? $curriculum->curriculumCourses->map(function ($cc) {
                    return [
                        'id' => $cc->id,
                        'course' => $cc->course ? [
                            'id' => $cc->course->id,
                            'code' => $cc->course->code,
                            'descriptive_title' => $cc->course->descriptive_title,
                        ] : null,
                        'category' => $cc->courseCategory ? [
                            'id' => $cc->courseCategory->id,
                            'name' => $cc->courseCategory->name,
                            'code' => $cc->courseCategory->code,
                        ] : null,
                        'semester' => $cc->semester ? [
                            'id' => $cc->semester->id,
                            'year' => $cc->semester->year,
                            'sem' => $cc->semester->sem,
                        ] : null,
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
                }) : [],
            ] : null,
            'proposal' => $this->whenLoaded('proposal', function () {
                return [
                    'id' => $this->proposal->id,
                    'status' => $this->proposal->status,
                    'comment' => $this->proposal->comment,
                    'version' => $this->proposal->version,
                    'created_at' => $this->proposal->created_at,
                    'updated_at' => $this->proposal->updated_at,
                ];
            }),
        ];
    }
}
