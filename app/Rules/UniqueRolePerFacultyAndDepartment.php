<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\User;
use App\Models\Role;

class UniqueRolePerFacultyAndDepartment implements ValidationRule
{
    protected $roleName;
    protected $facultyId;
    protected $departmentId;
    protected $userId;

    public function __construct($roleName, $facultyId, $departmentId, $userId = null)
    {
        $this->roleName = $roleName;
        $this->facultyId = $facultyId;
        $this->departmentId = $departmentId;
        $this->userId = $userId;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $role = Role::where('name', $this->roleName)->first();
        if (!$role) {
            $fail('Invalid role.');
            return;
        }

        if ($this->roleName === "Dean") {
            if (User::where('role_id', $role->id)
                ->where('faculty_id', $this->facultyId)
                ->where('id', '!=', $this->userId)
                ->exists()
            ) {
                $fail('There can only be one Dean per faculty.');
            }
        }

        if ($this->roleName === "Department") {
            if (User::where('role_id', $role->id)
                ->where('department_id', $this->departmentId)
                ->where('id', '!=', $this->userId)
                ->exists()
            ) {
                $fail('There can only be one Department head per department.');
            }
        }
    }
}
