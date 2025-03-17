<?php

namespace Tests\Unit\Requests\Api\V1\Department;

use Tests\TestCase;
use App\Models\Program;
use App\Models\ProgramEducationalObjective;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProgramEducationalObjectiveRequestTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    use RefreshDatabase;
    protected $departmentUser;
    protected $program;

    protected function setUp(): void
    {
        parent::setUp();

        // create a role
        Role::factory()->create(['id' => 3, 'name' => 'Department']);
        // create a program
        $this->program = Program::factory()->create();
        //  create user
        $this->departmentUser = User::factory()->create(['role_id' => '3']);
        $this->actingAs($this->departmentUser);
    }

    public function test_peo_requires_mandatory_fields()
    {
        $response = $this->postJson('api/v1/department/program-educational-objectives', [
            'statement' => '',
            'program_id' => null,

        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['statement', 'program_id']);
    }


    public function test_peo_requires_program_id_to_exist()
    {
        $response = $this->postJson('api/v1/department/program-educational-objectives', [
            'statement' => "sample statement",
            'program_id' => 999
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['program_id']);
    }

    public function test_peo_allows_valid_request()
    {
        $validData = [
            'statement' => 'valid statement',
            'program_id' => $this->program->id
        ];
        $response = $this->postJson('api/v1/department/program-educational-objectives', $validData);

        $response->assertStatus(201)->assertJsonStructure([
            'data',
            'message',
        ]);
    }


    public function test_peo_allows_partial_update()
    {
        $data = [
            'statement' => 'valid statement',
            'program_id' => $this->program->id
        ];
        $peo = ProgramEducationalObjective::factory()->create($data);
        $partialData = [
            'statement' => 'updated statement',
        ];
        $response = $this->putJson("api/v1/department/program-educational-objectives/{$peo->id}", $partialData);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'statement' => 'updated statement',
            ],
            'message' => 'PEO updated successfully'
        ]);
    }
}
