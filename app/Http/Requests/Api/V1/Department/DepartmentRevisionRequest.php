<?php

namespace App\Http\Requests\Api\V1\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'peos.*.id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        // If numeric, must exist in database
                        if (!$this->validateExistingId('program_educational_objectives', $value)) {
                            $fail('The selected PEO ID does not exist.');
                        }
                    } elseif (!is_string($value)) {
                        $fail('ID must be a numeric ID for existing entities or a string for new ones.');
                    }
                }
            ],
            'peos.*.statement' => 'required|string',

            // 3️ PEO to Mission Mapping
            'peo_mission_mappings' => 'array',
            'peo_mission_mappings.*.peo_id' => 'required',
            'peo_mission_mappings.*.mission_id' => 'required|exists:missions,id',

            // 4️ GA to PEO Mapping
            'ga_peo_mappings' => 'array',
            'ga_peo_mappings.*.peo_id' => 'required',
            'ga_peo_mappings.*.ga_id' => 'required|exists:graduate_attributes,id',

            // 5️ POs
            'pos' => 'array',
            'pos.*.id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        if (!$this->validateExistingId('program_outcomes', $value)) {
                            $fail('The selected PO ID does not exist.');
                        }
                    } elseif (!is_string($value)) {
                        $fail('ID must be a numeric ID for existing entities or a string for new ones.');
                    }
                }
            ],
            'pos.*.name' => 'required|string',
            'pos.*.statement' => 'required|string',

            // 6️ PO to PEO Mapping
            'po_peo_mappings' => 'array',
            'po_peo_mappings.*.po_id' => 'required',
            'po_peo_mappings.*.peo_id' => 'required',

            // 7 PO to GA Mapping
            'po_ga_mappings' => 'array',
            'po_ga_mappings.*.po_id' => 'required',
            'po_ga_mappings.*.ga_id' => 'required|exists:graduate_attributes,id',

            // 8 Curriculum
            'curriculum.name' => 'sometimes|required|string|max:255',

            // 9 Course Categories
            'course_categories' => 'array',
            'course_categories.*.id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null) {
                        if (is_numeric($value)) {
                            if (!$this->validateExistingId('course_categories', $value)) {
                                $fail('The selected category ID does not exist.');
                            }
                        } elseif (!is_string($value)) {
                            $fail('ID must be a numeric ID for existing entities, null, or a string for new ones.');
                        }
                    }
                }
            ],
            'course_categories.*.name' => 'required|string',
            'course_categories.*.code' => 'required|string',

            // 10 Curriculum Courses
            'curriculum_courses' => 'array',
            'curriculum_courses.*.id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value !== null) {
                        if (is_numeric($value)) {
                            if (!$this->validateExistingId('curriculum_courses', $value)) {
                                $fail('The selected curriculum course ID does not exist.');
                            }
                        } elseif (!is_string($value)) {
                            $fail('ID must be a numeric ID for existing entities, null, or a string for new ones.');
                        }
                    }
                }
            ],
            'curriculum_courses.*.course_id' => 'required',
            'curriculum_courses.*.course_code' => 'required_if:curriculum_courses.*.course_id,null|string',
            'curriculum_courses.*.course_title' => 'required_if:curriculum_courses.*.course_id,null|string',
            'curriculum_courses.*.course_category_id' => 'required_without:curriculum_courses.*.category_code',
            'curriculum_courses.*.category_code' => 'required_without:curriculum_courses.*.course_category_id|string',
            'curriculum_courses.*.semester_id' => 'required|exists:semesters,id',
            'curriculum_courses.*.unit' => 'required|numeric',

            // 11 Curriculum Course to PO Mappings
            'course_po_mappings' => 'array',
            'course_po_mappings.*.curriculum_course_id' => 'required',
            'course_po_mappings.*.po_id' => 'required',
            'course_po_mappings.*.ied' => 'required|array|min:1'
        ];
    }

    /**
     * Helper method to validate if a numeric ID exists in the given table
     */
    private function validateExistingId($table, $id)
    {
        if (!is_numeric($id)) {
            return false;
        }
        return DB::table($table)->where('id', $id)->exists();
    }
}
