<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Semester;

class SemesterRequest extends FormRequest
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

    /* Need this */
    /** @var \Illuminate\Http\Request $this */

    public function rules(): array
    {
        $semesterId = request()->route('semester');
        return [
            'year' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'sem' => [
                'sometimes',
                'required',
                'string',
                Rule::in(Semester::getValidSemesterNames()),
                // Unique combination of year and name
                Rule::unique('semesters')
                    ->where(function ($query) {
                        return $query->where('year', request()->input('year'));
                    })
                    ->ignore($semesterId),
            ],

        ];
    }

    public function messages(): array
    {
        return [
            'name.in' => 'The semester name must be one of: First, Second, or Midyear.',
            'name.unique' => 'A semester with this year and name combination already exists.',
        ];
    }
}
