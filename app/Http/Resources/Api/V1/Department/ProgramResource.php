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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'status' => $this->status,
            'version' => $this->version,
            'department' => new DepartmentResource($this->whenLoaded('department')),


        ];
    }
}
