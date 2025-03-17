<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramEducationalObjectiveRequest extends FormRequest
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
        $peoID = $this->route('program-educational-objective');
        return [
            'statement' => [
                'sometimes',
                'required',
                'string',
            ],
            'program_id' => [
                'sometimes',
                'required',
                Rule::exists('programs', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'statement.required' => 'The statement is required.',
            'statement.string' => 'The statement must be a string.',
            'program_id.required' => 'The program ID is required.',
            'program_id.exists' => 'The selected program ID is invalid.',
        ];
    }
}
