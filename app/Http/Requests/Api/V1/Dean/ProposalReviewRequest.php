<?php

namespace App\Http\Requests\Api\V1\Dean;

use Illuminate\Foundation\Http\FormRequest;

class ProposalReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'status' => 'required|in:approved,revision',
        ];

        if (request()->input('status') === 'revision') {
            $rules['department_level'] = 'present|array';
            $rules['committee_level'] = 'present|array';

            // Field validations when items exist
            $rules['department_level.*.section'] = 'required|string|in:program,peos,peo_mission_mappings,ga_peo_mappings,pos,po_peo_mappings,curriculum,course_categories,curriculum_courses,course_po_mappings';
            $rules['department_level.*.details'] = 'required|string';

            $rules['committee_level.*.curriculum_course_id'] = 'required|exists:curriculum_courses,id';
            $rules['committee_level.*.details'] = 'required|string';
        }

        return $rules;
    }

    /**
     * Configure the validator instance.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @return void
     */
    public function withValidator($validator)
    {
        if (request()->input('status') === 'revision') {
            $validator->after(function ($validator) {
                $departmentLevel = request()->input('department_level', []);
                $committeeLevel = request()->input('committee_level', []);

                if (empty($departmentLevel) && empty($committeeLevel)) {
                    $validator->errors()->add('revision', 'At least one of department_level or committee_level must have items when status is revision.');
                }
            });
        }
    }
}