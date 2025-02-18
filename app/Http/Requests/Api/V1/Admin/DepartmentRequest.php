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
            'name'=>[
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('departments','name')->ignore($this->route('department')),
            ],
            'abbreviation'=>[
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('departments','abbreviation')->ignore($this->route('department')),
            ],
            'faculty_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('faculties','id'),
            ],
        ];
    }
}