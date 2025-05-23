<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramOutcomeRequest extends FormRequest
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
        $userId = request()->route('program-outcome');
        return [
            //
            'program_proposal_id' => [
                'sometimes',
                'required',
                Rule::exists('program_proposals', 'id')

            ],
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('program_outcomes', 'name')->ignore($userId),
            ],
            'statement' => [
                'sometimes',
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'program_proposal_id.required' => 'The program proposal ID is required.',
            'program_proposal_id.exists' => 'The selected program proposal ID is invalid.',
            'name.required' => 'The name is required.',
            'name.string' => 'The name must be a string.',
            'name.max' => 'The name may not be greater than 50 characters.',
            'name.unique' => 'The name has already been taken.',
            'statement.required' => 'The statement is required.',
            'statement.string' => 'The statement must be a string.',
        ];
    }
}
