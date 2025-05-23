<?php

namespace App\Http\Requests\Api\V1\Faculty;

use Illuminate\Foundation\Http\FormRequest;

class CourseDetailsWizardRequest extends FormRequest
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
            'course_outcomes' => 'required|array|min:1',

            'course_outcomes.*.name' => 'required|string',
            'course_outcomes.*.statement' => 'required|string',

            'course_outcomes.*.abcd.audience' => 'required|string',
            'course_outcomes.*.abcd.behavior' => 'required|string',
            'course_outcomes.*.abcd.condition' => 'required|string',
            'course_outcomes.*.abcd.degree' => 'required|string',

            'course_outcomes.*.cpa' => 'required|in:C,P,A',

            'course_outcomes.*.po_mappings' => 'required|array|min:1',
            'course_outcomes.*.po_mappings.*.po_id' => 'required|exists:program_outcomes,id',
            'course_outcomes.*.po_mappings.*.ied' => 'required|in:I,E,D',


            'course_outcomes.*.tla_tasks' => 'required|array|min:1',
            'course_outcomes.*.tla_tasks.*.at_code' => 'required|string',
            'course_outcomes.*.tla_tasks.*.at_name' => 'required|string',
            'course_outcomes.*.tla_tasks.*.at_tool' => 'required|string',
            'course_outcomes.*.tla_tasks.*.at_weight' => 'required|numeric',

            'course_outcomes.*.tla_assessment_method.teaching_methods' => 'required|array|min:1',
            'course_outcomes.*.tla_assessment_method.learning_resources' => 'required|array|min:1',



        ];
    }
}