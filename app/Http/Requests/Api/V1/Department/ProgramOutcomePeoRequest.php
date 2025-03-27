<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Validation\Rule;

use Illuminate\Foundation\Http\FormRequest;

class ProgramOutcomePeoRequest extends FormRequest
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
            'peo_ids' => [
                'array',
                'required',
            ],
            'peo_ids.*' => [
                Rule::exists('program_educational_objectives', 'id'),
            ],

        ];
    }
}
