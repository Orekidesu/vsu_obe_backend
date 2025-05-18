<?php

namespace App\Http\Requests\Api\V1\Faculty;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommitteeRevisionRequest extends FormRequest
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
            'curriculum_course_id' => 'required|exists:curriculum_courses,id',

            // Course Outcome
            'course_outcomes' => 'required|array',

            // CO Fields
            'course_outcomes.*.id' => 'nullable|exists:course_outcomes,id',
            'course_outcomes.*.name' => 'required|string',
            'course_outcomes.*.statement' => 'required|string',

            // CO-ABCD  Model
            'course_outcomes.*.abcd' => 'required|array',
            'course_outcomes.*.abcd.audience' => 'required|string',
            'course_outcomes.*.abcd.behavior' => 'required|string',
            'course_outcomes.*.abcd.condition' => 'required|string',
            'course_outcomes.*.abcd.degree' => 'required|string',

            // CO-CPA Classification
            'course_outcomes.*.cpa' => 'required|in:C,P,A',

            // CO-PO Mappings
            'course_outcomes.*.co_po_mappings' => 'required|array|min:1',
            'course_outcomes.*.co_po_mappings.*.po_id' => 'required|integer|exists:program_outcomes,id',
            'course_outcomes.*.co_po_mappings.*.ied' => 'required|in:I,E,D',

            // Teaching-Learning-Assessment(TLA) Tasks
            'course_outcomes.*.tla_tasks' => 'required|array|min:1',
            'course_outcomes.*.tla_tasks.*.id' => 'nullable|integer|exists:tla_tasks,id',
            'course_outcomes.*.tla_tasks.*.at_code' => 'required_with:course_outcomes.*.tla_tasks|string',
            'course_outcomes.*.tla_tasks.*.at_name' => 'required_with:course_outcomes.*.tla_tasks|string',
            'course_outcomes.*.tla_tasks.*.at_tool' => 'required_with:course_outcomes.*.tla_tasks|string',
            'course_outcomes.*.tla_tasks.*.at_weight' => 'required_with:course_outcomes.*.tla_tasks|numeric',

            // TLA Assessment Methods
            'course_outcomes.*.tla_assessment_method.*.id' => 'nullable|integer|exists:tla_methods,id',
            'course_outcomes.*.tla_assessment_method' => 'required|array',
            'course_outcomes.*.tla_assessment_method.teaching_methods' => 'array|min:1',
            'course_outcomes.*.tla_assessment_method.learning_resources' => 'array|min:1',


        ];
    }
}
