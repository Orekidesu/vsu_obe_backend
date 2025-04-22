<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DepartmentRequest extends FormRequest
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
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('departments', 'name')->ignore($this->route('department')),
            ],
            'abbreviation' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('departments', 'abbreviation')->ignore($this->route('department')),
            ],
            'faculty_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('faculties', 'id'),
            ],
        ];
    }
    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // for name
            'name.required' => 'The department name is required.',
            'name.string' => 'The department name must be a string.',
            'name.max' => 'The department name must not exceed 255',
            'name.unique' => 'The department name has already been taken.',
            // for abbreviation
            'abbreviation.required' => 'The abbreviation is required.',
            'abbreviation.string' => 'The abbreviation must be a string.',
            'abbreviation.max' => 'The abbreviation may not be greater than 10 characters.',
            'abbreviation.unique' => 'The abbreviation has already been taken.',
            // for faculty id
            'faculty_id.required' => 'The faculty ID is required.',
            'faculty_id.integer' => 'The faculty ID must be an integer.',
            'faculty_id.exists' => 'The selected faculty ID is invalid.',
        ];
    }
}