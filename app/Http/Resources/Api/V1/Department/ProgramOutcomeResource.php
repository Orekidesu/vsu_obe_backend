<?php

namespace App\Http\Resources\Api\V1\Department;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramOutcomeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            // 'program' => new ProgramResource($this->programProposal->program),
            'program' => $this->whenLoaded('programProposal', function () {
                return new ProgramResource($this->programProposal->program);
            }),

            'ied' => $this->whenPivotLoaded('curriculum_course_po', function () {
                return json_decode($this->pivot->ied);
            }),
            'name' => $this->name,
            'statement' => $this->statement,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
