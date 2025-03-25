<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
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
        $programId = $this->route('program');
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
            'abbreviation' => [
                'sometimes',
                'required',
                'string',
                'max:20',
            ],
            'department_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('departments', 'id'),
            ],
        ];
    }

    /**
     * Get the custom error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'The program name is required.',
            'name.string' => 'The program name must be a string.',
            'name.max' => 'The program name may not be greater than 50 characters.',
            'name.unique' => 'The program name has already been taken.',
            'abbreviation.required' => 'The abbreviation is required.',
            'abbreviation.string' => 'The abbreviation must be a string.',
            'abbreviation.max' => 'The abbreviation may not be greater than 20 characters.',
            'abbreviation.unique' => 'The abbreviation has already been taken.',
            'department_id.required' => 'The department ID is required.',
            'department_id.integer' => 'The department ID must be an integer.',
            'department_id.exists' => 'The selected department ID is invalid.',
        ];
    }
}
