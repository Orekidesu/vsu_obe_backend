<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            //
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'max:50'
            ],
            'last_name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('user')),
            ],
            'password' => [
                'sometimes',
                'required',
                'confirmed',
                'min:8',
            ],
            'role_id' => [
                'sometimes',
                'required',
                Rule::exists('roles', 'id'),
            ],
            'faculty_id' => [
                'sometimes',
                'required',
                Rule::exists('faculties', 'id'),
            ],
            'department_id' => [
                'sometimes',
                'required',
                Rule::exists('departments', 'id'),
            ],


        ];
    }
}
