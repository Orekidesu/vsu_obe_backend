<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramProposalWizardRequest extends FormRequest
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

            // Program
            'program.name' => [
                'required',
                'string',
                'max:50',
            ],
            'program.abbreviation' => [
                'required',
                'string',
                'max:20',
            ],
            'program.department_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('departments', 'id'),
            ],

            // Program Educational Objectives
            'peos' => [
                'required',
                'array',
                'min:1'
            ],
            'peos.*.statement' => [
                'required',
                'string',
                'max:100',
            ],

            // PEO to Mission
            'peo_mission_mappings' => [
                'required',
                'array',
                'min:1'
            ],
            'peo_mission_mappings.*.peo_index' => [
                'required',
                'integer',
                'min:0'
            ],
            'peo_mission_mappings.*.mission_id' => [
                'required',
                Rule::exists('missions', 'id'),
            ],

            // PEO to Graduate Attribute
            'ga_peo_mappings' => [
                'required',
                'array',
                'min:1',
            ],
            'ga_peo_mappings.*.peo_index' => [
                'required',
                'integer',
                'min:0',
            ],
            'ga_peo_mappings.*.ga_id' => [
                'required',
                Rule::exists('graduate_attributes', 'id'),
            ],

            // Program Outcomes
            'pos' => [
                'required',
                'array',
                'min:1',
            ],
            'pos.*.name' => [
                'required',
                'string',
                'max:50',
            ],
            'pos.*.statement' => [
                'required',
                'string',
            ],

            // PO to PEO 
            'po_peo_mappings' => [
                'required',
                'array',
                'min:1'
            ],
            'po_peo_mappings.*.po_index' => [
                'required',
                'integer',
                'min:0'
            ],
            'po_peo_mappings.*.peo_index' => [
                'required',
                'integer',
                'min:0'
            ],

            // PO to GA 
            'po_ga_mappings' => [
                'required',
                'array',
                'min:1'
            ],
            'po_ga_mappings.*.po_index' => [
                'required',
                'integer',
                'min:0'
            ],
            'po_ga_mappings.*.ga_id' => [
                'required',
                Rule::exists('graduate_attributes', 'id'),
            ],

            // Curriculum
            'curriculum.name' => [
                'required',
                'string',
                'max:255'
            ],

            // Semester
            'semesters' => [
                'required',
                'array',
                'min:1'
            ],
            'semesters.*.year' => [
                'required',
                'integer',
                'min:1'
            ],
            'semesters.*.sem' =>
            [
                'required',
                'string'
            ],

            // Course Categories
            'course_categories' => [
                'required',
                'array',
                'min:1'
            ],
            'course_categories.*.name' => [
                'required',
                'string',
                'max:255'
            ],
            'course_categories.*.code' => [
                'required',
                'string',
                'max:50'
            ],

            // Courses
            'courses' => [
                'required',
                'array',
                'min:1'
            ],
            'courses.*.code' => [
                'required',
                'string',
                'max:20'
            ],
            'courses.*.descriptive_title' => [
                'required',
                'string',
                'max:255'
            ],

            // Curriculum Courses

            'curriculum_courses' => [
                'required',
                'array',
                'min:1'
            ],
            'curriculum_courses.*.course_code' => [
                'required',
                'string'
            ],
            'curriculum_courses.*.category_code' => [
                'required',
                'string'
            ],
            'curriculum_courses.*.semester_year' => [
                'required',
                'integer',
                'min:1'
            ],
            'curriculum_courses.*.semester_name' => [
                'required',
                'string'
            ],
            'curriculum_courses.*.units' => [
                'required',
                'numeric',
                'min:0'
            ],


            // Curriculum Course to PO (IRD)
            'course_po_mappings' => [
                'required',
                'array',
                'min:1'
            ],
            'course_po_mappings.*.course_code' => [
                'required',
                'string'
            ],
            'course_po_mappings.*.po_code' => [
                'required',
                'string'
            ],
            'course_po_mappings.*.ird' => [
                'required',
                'array',
                'min:1'
            ],
            'course_po_mappings.*.ird.*' => [
                'required',
                'in:I,R,D'
            ],










        ];
    }
}