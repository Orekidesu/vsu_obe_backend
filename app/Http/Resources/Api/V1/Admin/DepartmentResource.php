<?php

namespace App\Http\Resources\Api\V1\Admin;

use App\Http\Resources\Api\V1\Department\ProgramResource;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
            [
                'id' => $this->id,
                'name' => $this->name,
                'abbreviation' => $this->abbreviation,
                // 'faculty' => $this->faculty,
                'faculty' => new FacultyResource($this->whenLoaded('faculty')),
                'programs' => ProgramResource::collection($this->whenLoaded('program')),

            ];
    }
}
