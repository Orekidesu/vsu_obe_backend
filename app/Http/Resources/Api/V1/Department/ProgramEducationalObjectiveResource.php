<?php

namespace App\Http\Resources\Api\V1\Department;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramEducationalObjectiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'statement' => $this->statement,
            'program' => new ProgramResource($this->whenLoaded('program')),
        ];
    }
}
