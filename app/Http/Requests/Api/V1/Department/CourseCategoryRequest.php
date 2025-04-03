<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    /** @var \Illuminate\Http\Request $this */
    public function rules(): array
    {
        $courseCategoryId = request()->route('course_category');
        $curriculumId = $this->input('curriculum_id'); // Get curriculum_id safely

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('course_categories', 'name')
                    ->where('curriculum_id', $curriculumId)
                    ->ignore($courseCategoryId),
            ],
            'code' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('course_categories', 'code')
                    ->where('curriculum_id', $curriculumId)
                    ->ignore($courseCategoryId),
            ],
            'curriculum_id' => [
                'sometimes',
                'required',
                Rule::exists('curricula', 'id'),
            ],
        ];
    }
}
