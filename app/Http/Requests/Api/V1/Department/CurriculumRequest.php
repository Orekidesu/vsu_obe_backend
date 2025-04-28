<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CurriculumRequest extends FormRequest
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
        $curriculumId = request()->route('curriculum');

        return [
            'program_proposal_id' => [
                'sometimes',
                'required',
                Rule::exists('program_proposal_id', 'id'),
                Rule::unique('curricula', 'program_id')->ignore($curriculumId),

            ],
            'name' => [
                'sometimes',
                'required',
                'string',
            ],
            //
        ];
    }
}
