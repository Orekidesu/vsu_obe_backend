<?php

namespace App\Http\Resources\Api\V1\Admin;

use App\Http\Resources\Api\V1\Department\ProgramEducationalObjectiveResource;
use App\Models\ProgramEducationalObjective;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tests\Feature\Api\V1\Department\ProgramEducationalTest;

class GraduateAttributeResource extends JsonResource
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
            'ga_no' => $this->ga_no,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
