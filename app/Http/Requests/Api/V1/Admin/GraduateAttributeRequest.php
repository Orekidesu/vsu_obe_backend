<?php

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GraduateAttributeRequest extends FormRequest
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
            'ga_no'=>[
                'sometimes',
                'required',
                'integer',
                Rule::unique('graduate_attributes','ga_no')->ignore($this->route('graduate_attribute')),
            ],
            'description'=>[
                'sometimes',
                'required',
                'string',
            ],
        ];
    }
}