<?php

namespace Tests\Feature\Api\V1\Department;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Department;
use App\Models\Program;
use App\Models\Role;

class ProgramTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $departmentUser;
    protected $department;
    protected $program;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role
        Role::factory()->create(['id' => 3, 'name' => 'Department']);
        Role::factory()->create(['id' => 2, 'name' => 'Dean']);

        // create a department
        $this->department = Department::factory()->create();

        // Create a department user
        $this->departmentUser = User::factory()->create(['role_id' => 3]);
        $this->actingAs($this->departmentUser);

        // Create a program
        $this->program = Program::factory()->create([
            'name' => 'Bachelor of Science in Computer Science',
            'abbreviation' => 'BSCS',
            'department_id' => $this->department->id,
        ]);
    }

    // test department user can retrieve the list of programs

    public function test_department_user_can_retrieve_all_programs()
    {
        $response = $this->getJson('api/v1/department/programs');


        $response->assertStatus(200)->assertJsonStructure([
            'data',
            'message',
        ]);
    }


    // test if a department user can retrieve a single program
    public function test_department_user_can_retrieve_program()
    {
        $response = $this->getJson("/api/v1/department/programs/{$this->program->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message',

        ]);
    }

    // test if a department user can create a program
    public function test_department_user_can_create_program()
    {
        $response = $this->postJson('/api/v1/department/programs', [
            'name' => 'Bachelor of Science in Information Technology',
            'abbreviation' => 'BSIT',
            'department_id' => $this->department->id,
        ]);

        $response->assertStatus(201);

        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test if a department user can update a program
    public function test_department_user_can_update_program()
    {
        $response = $this->putJson("/api/v1/department/programs/{$this->program->id}", [
            'abbreviation' => 'BSCS'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test if a department user can delete a program
    public function test_department_user_can_delete_program()
    {
        // $newProgram = Program::factory()->create(['department_id' => $this->department->id]);
        $response = $this->deleteJson("/api/v1/department/programs/{$this->program->id}");

        $response->assertStatus(200);
        $response->json([
            'message' => 'program deleted successfully',
        ]);

        $this->assertDatabaseMissing('programs', ['id' => $this->program->id]);
    }


    // test if a user without a department role cannot access program routes
    public function test_non_department_user_cannot_access_programs()
    {
        $nonDepartmentUser = User::factory()->create(['role_id' => 2]);
        $this->actingAs($nonDepartmentUser);

        $response = $this->getJson('/api/v1/department/programs');

        $response->assertStatus(403); //unauthorized
    }
}
