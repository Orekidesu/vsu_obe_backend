<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
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
    public function rules(): array
    {
        $courseId = request()->route('course');
        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('courses', 'code')

            ],
            'descriptive_title' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('courses', 'descriptive_title')

            ],
        ];
    }
}