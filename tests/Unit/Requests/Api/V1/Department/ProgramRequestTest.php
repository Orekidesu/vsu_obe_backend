<?php

namespace Tests\Unit\Requests\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\Role;

class ProgramRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $departmentUser;
    protected $department;

    /**
     * Set up test environment before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a department
        $this->department = Department::factory()->create(['id' => 2]);

        // Create a department user (not admin)
        Role::factory()->create(['id' => 3, 'name' => 'Department']);
        $this->departmentUser = User::factory()->create(['role_id' => 3]); // Assuming 2 is "Department" role
        $this->actingAs($this->departmentUser); // Authenticate as Department role
    }

    /**
     * @test
     * Ensure required fields are validated when creating a program.
     */
    public function test_requires_mandatory_fields()
    {
        // make a post request and initialize the input to nulls and empty string
        // since out request have 'sometimes' rule, when the field is in the request, it should not be empty
        $response = $this->postJson('/api/v1/department/programs', ['name' => '', 'abbreviation' => '', 'department_id' => null]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'abbreviation', 'department_id']);
    }

    /**
     * @test
     * Ensure the 'name' field must be unique.
     */
    public function test_enforces_unique_name()
    {
        Program::factory()->create([
            'name' => 'Bachelor of Science in Computer Science',
        ]);

        $response = $this->postJson('/api/v1/department/programs', [
            'name' => 'Bachelor of Science in Computer Science', // Duplicate name
            'abbreviation' => 'CS',
            'department_id' => $this->department->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * @test
     * Ensure the 'abbreviation' field must be unique.
     */
    public function test_enforces_unique_abbreviation()
    {
        Program::factory()->create([
            // 'name' => 'Bachelor of Science in Computer Science',
            'abbreviation' => 'CS',
            // 'department_id' => $this->department->id
        ]);

        $response = $this->postJson('/api/v1/department/programs', [
            'name' => 'New Program',
            'abbreviation' => 'CS', // Duplicate abbreviation
            'department_id' => $this->department->id,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['abbreviation']);
    }

    /**
     * @test
     * Ensure 'department_id' exists in the departments table.
     */
    public function test_requires_department_id_to_exist()
    {
        $response = $this->postJson('/api/v1/department/programs', [
            'name' => 'Software Engineering',
            'abbreviation' => 'SE',
            'department_id' => 999, // Non-existing department ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['department_id']);
    }

    /**
     * @test
     * Ensure a program can be successfully created with valid data.
     */
    public function test_allows_valid_data()
    {
        $response = $this->postJson('/api/v1/department/programs', [
            'name' => 'Information Technology',
            'abbreviation' => 'IT',
            'department_id' => $this->department->id,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'data' => [
                    'name' => 'Information Technology',
                    'abbreviation' => 'IT',
                ],
                'message' => 'program created successfully'
            ]);

        $this->assertDatabaseHas('programs', ['name' => 'Information Technology']);
    }

    /**
     * @test
     * Ensure optional fields can be updated individually.
     */
    public function test_allows_optional_fields_on_update()
    {
        $program = Program::factory()->create([
            'name' => 'Old Program Name',
            'abbreviation' => 'OPN',
            'department_id' => $this->department->id,
        ]);

        $response = $this->putJson("/api/v1/department/programs/{$program->id}", [
            'name' => 'Updated Program Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Program Name',
                ],
                'message' => 'program updated successfully'
            ]);

        $this->assertDatabaseHas('programs', ['id' => $program->id, 'name' => 'Updated Program Name']);
    }
}
