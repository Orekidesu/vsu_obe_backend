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
        return [
            'id' => $this->id,
            'abbreviation' => $this->abbreviation,
            'status' => $this->status,
            'version' => $this->version,
            'comment' => $this->comment,
            'program' => ProgramResource::collection($this->whenLoaded('program')),
        ];
    }
}