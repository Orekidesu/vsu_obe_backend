<?php

namespace App\Http\Requests\Api\V1\Dean;

use Illuminate\Foundation\Http\FormRequest;

class ProposalReviewRequest extends FormRequest
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
            'status' => 'required|in:approved,revision',
            // Rules for revision
            'department_level' => 'required_if:status,revision|array',
            'department_level.*.section' => 'required|string|in:program,peos,peo_mission_mappings,ga_peo_mappings,pos,po_peo_mappings,curriculum,course_categories,curriculum_courses,course_po_mappings',
            'department_level.*.details' => 'required|string',

            'committee_level' => 'required_if:status,revision|array',
            'committee_level.*.curriculum_course_id' => 'required|exists:curriculum_courses,id',
            'committee_level.*.details' => 'required|string',
        ];
    }
}