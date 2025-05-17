<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentRevisionRequest extends FormRequest
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
            // 1️ Program Section
            'program.name' => 'sometimes|required|string|max:255',
            'program.abbreviation' => 'sometimes|required|string|max:20',

            // 2️ PEOs
            'peos' => 'array',
            'peos.*.id' => 'nullable|exists:program_educational_objectives,id',
            'peos.*.statement' => 'required|string',

            // 3️ PEO to Mission Mapping
            'peo_mission_mappings' => 'array',
            'peo_mission_mappings.*.peo_id' => 'required|exists:program_educational_objectives,id',
            'peo_mission_mappings.*.mission_id' => 'required|exists:missions,id',

            // 4️ GA to PEO Mapping
            'ga_peo_mappings' => 'array',
            'ga_peo_mappings.*.peo_id' => 'required|exists:program_educational_objectives,id',
            'ga_peo_mappings.*.ga_id' => 'required|exists:graduate_attributes,id',

            // 5️ POs
            'pos' => 'array',
            'pos.*.id' => 'nullable|exists:program_outcomes,id',
            'pos.*.name' => 'required|string',
            'pos.*.statement' => 'required|string',

            // 6️ PO to PEO Mapping
            'po_peo_mappings' => 'array',
            'po_peo_mappings.*.po_id' => 'required|exists:program_outcomes,id',
            'po_peo_mappings.*.peo_id' => 'required|exists:program_educational_objectives,id',

            // 7 PO to GA Mapping
            'po_ga_mappings' => 'array',
            'po_ga_mappings.*.po_id' => 'required|exists:program_outcomes,id',
            'po_ga_mappings.*.ga_id' => 'required|exists:graduate_attributes,id',

            // 8 Curriculum
            'curriculum.name' => 'sometimes|required|string|max:255',

            // 9 Course Categories
            'course_categories' => 'array',
            'course_categories.*.id' => 'nullable|exists:course_categories,id',
            'course_categories.*.name' => 'required|string',
            'course_categories.*.code' => 'required|string',

            // 10 Curriculum Courses
            'curriculum_courses' => 'array',
            'curriculum_courses.*.id' => 'nullable|exists:curriculum_courses,id',
            'curriculum_courses.*.course_id' => 'required|exists:courses,id',
            'curriculum_courses.*.course_category_id' => 'required|exists:course_categories,id',
            'curriculum_courses.*.semester_id' => 'required|exists:semesters,id',
            'curriculum_courses.*.unit' => 'required|numeric',

            // 11  Curriculum Course to PO Mappings
            'course_po_mappings' => 'array',
            'course_po_mappings.*.curriculum_course_id' => 'required|exists:curriculum_courses,id',
            'course_po_mappings.*.po_id' => 'required|exists:program_outcomes,id',
            'course_po_mappings.*.ird' => 'required|array|min:1'
        ];
    }
}
