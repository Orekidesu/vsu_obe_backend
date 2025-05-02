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
        // Find the relevant proposal from already loaded proposals
        $relevantProposal = null;

        if ($this->relationLoaded('proposal') && $this->proposal->isNotEmpty()) {
            // Get appropriate proposal based on program status
            if ($this->status === 'pending') {
                $relevantProposal = $this->proposal->where('status', 'pending')->sortByDesc('created_at')->first();
            } else if ($this->status === 'revision') {
                $relevantProposal = $this->proposal->where('status', 'revision')->sortByDesc('created_at')->first();
            } else {
                $relevantProposal = $this->proposal->where('status', 'approved')->sortByDesc('created_at')->first();
            }

            // Fallback to latest proposal if no matching status is found
            if (!$relevantProposal) {
                $relevantProposal = $this->proposal->sortByDesc('created_at')->first();
            }
        }

        // Get PEOs, POs and curriculum from the proposal (if it exists)
        $peos = $relevantProposal && $relevantProposal->relationLoaded('peos') ? $relevantProposal->peos : collect([]);
        $pos = $relevantProposal && $relevantProposal->relationLoaded('pos') ? $relevantProposal->pos : collect([]);
        $curriculum = $relevantProposal && $relevantProposal->relationLoaded('curriculum') ? $relevantProposal->curriculum : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'status' => $this->status,
            'version' => $this->version,
            'department' => $this->whenLoaded('department', fn() => new DepartmentResource($this->department)),
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'peos' => $peos->map(function ($peo) {
                return [
                    'id' => $peo->id,
                    'statement' => $peo->statement,
                    'missions' => $peo->relationLoaded('missions') ? $peo->missions->map(function ($mission) {
                        return [
                            'id' => $mission->id,
                            'mission_no' => $mission->mission_no,
                            'description' => $mission->description,
                        ];
                    })->all() : [],
                    'graduate_attributes' => $peo->relationLoaded('gas') ? $peo->gas->map(function ($ga) {
                        return [
                            'id' => $ga->id,
                            'ga_no' => $ga->ga_no,
                            'name' => $ga->name,
                        ];
                    })->all() : [],
                ];
            }),
            'pos' => $pos->map(function ($po) {
                return [
                    'id' => $po->id,
                    'name' => $po->name,
                    'statement' => $po->statement,
                    'peos' => $po->relationLoaded('peos') ? $po->peos->map(function ($peo) {
                        return [
                            'id' => $peo->id,
                            'statement' => $peo->statement,
                        ];
                    })->all() : [],
                    'graduate_attributes' => $po->relationLoaded('gas') ? $po->gas->map(function ($ga) {
                        return [
                            'id' => $ga->id,
                            'ga_no' => $ga->ga_no,
                            'name' => $ga->name,
                        ];
                    })->all() : [],
                ];
            }),
            'curriculum' => $curriculum ? [
                'id' => $curriculum->id,
                'name' => $curriculum->name,
                'courses' => $curriculum->relationLoaded('curriculumCourses') ? $curriculum->curriculumCourses->map(function ($cc) {
                    return [
                        'id' => $cc->id,
                        'course' => $cc->relationLoaded('course') ? [
                            'id' => $cc->course->id,
                            'code' => $cc->course->code,
                            'descriptive_title' => $cc->course->descriptive_title,
                        ] : null,
                        'category' => $cc->relationLoaded('courseCategory') ? [
                            'id' => $cc->courseCategory->id,
                            'name' => $cc->courseCategory->name,
                            'code' => $cc->courseCategory->code,
                        ] : null,
                        'semester' => $cc->relationLoaded('semester') ? [
                            'id' => $cc->semester->id,
                            'year' => $cc->semester->year,
                            'sem' => $cc->semester->sem,
                        ] : null,
                        'units' => $cc->unit,
                        'po_mappings' => $cc->relationLoaded('pos') ? $cc->pos->map(function ($po) {
                            $pivot = $po->pivot;
                            return [
                                'po_id' => $po->id,
                                'po_name' => $po->name,
                                'ied' => json_decode($pivot->ied),
                            ];
                        })->all() : [],
                    ];
                })->all() : [],
            ] : null,
            'proposal' => $relevantProposal ? [
                'id' => $relevantProposal->id,
                'status' => $relevantProposal->status,
                'comment' => $relevantProposal->comment,
                'version' => $relevantProposal->version,
                'created_at' => $relevantProposal->created_at,
                'updated_at' => $relevantProposal->updated_at,
            ] : null,
        ];
    }
}