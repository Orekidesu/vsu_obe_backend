<?php

namespace App\Http\Resources\Api\V1\Department;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurriculumCourseResource extends JsonResource
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
            'curriculum' => new CurriculumResource($this->whenLoaded('curriculum')),
            'course' => new CourseResource($this->whenLoaded('course')),
            'course_category' => new CourseCategoryResource($this->whenLoaded('courseCategory')),
            'semester' => new SemesterResource($this->whenLoaded('semester')),
            'units' => $this->unit,
        ];
    }
}