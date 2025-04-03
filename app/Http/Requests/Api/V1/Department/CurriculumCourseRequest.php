<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurriculumCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'curriculum_id' => [
                'sometimes',
                'required',
                Rule::exists('curricula', 'id'),
            ],
            'course_id' => [
                'sometimes',
                'required',
                Rule::exists('courses', 'id'),
            ],
            'course_category_id' => [
                'sometimes',
                'required',
                Rule::exists('course_categories', 'id'),
            ],
            'semester_id' => [
                'sometimes',
                'required',
                Rule::exists('semesters', 'id'),
            ],
            'unit' => [
                'sometimes',
                'required',
                'numeric',
                'min:1.0',
                'max:10.0',
            ],
        ];
    }
}