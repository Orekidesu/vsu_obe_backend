<?php

namespace App\Http\Resources\Api\V1\Department;

use App\Http\Resources\Api\V1\Faculty\CourseOutcomeResource;
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
            'is_in_revision' => $this->whenLoaded('committees', function () {
                return $this->committees->contains(function ($committee) {
                    return $committee->pivot->is_in_revision;
                });
            }),
            'is_completed' => $this->whenLoaded('committees', function () {
                return $this->committees->contains(function ($committee) {
                    return $committee->pivot->is_completed;
                });
            }),

            'course_outcomes' => $this->whenLoaded('cos', function () {
                return CourseOutcomeResource::collection($this->cos);
            }),


        ];
    }
}
