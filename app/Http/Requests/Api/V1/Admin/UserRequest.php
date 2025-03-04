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
    public function messages(): array
    {
        return [
            'first_name.required' => 'The first name is required.',
            'last_name.required' => 'The last name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password is required.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
            'role_id.required' => 'The role ID is required.',
            'role_id.exists' => 'The selected role ID is invalid.',
            'faculty_id.required' => 'The faculty ID is required.',
            'faculty_id.exists' => 'The selected faculty ID is invalid.',
            'department_id.required' => 'The department ID is required.',
            'department_id.exists' => 'The selected department ID is invalid.',
        ];
    }
}
