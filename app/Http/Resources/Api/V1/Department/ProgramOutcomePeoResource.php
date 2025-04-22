<?php

namespace App\Http\Resources\Api\V1\Department;

use App\Models\ProgramEducationalObjective;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgramOutcomePeoResource extends JsonResource
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
            'name' => $this->name,
            'statement' => $this->statement,
            'peos' => ProgramEducationalObjectiveResource::collection($this->whenLoaded('peos')),
        ];
    }
}
