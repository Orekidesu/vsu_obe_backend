<?php

namespace Tests\Feature\Api\V1\Department;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\ProgramEducationalObjective;
use App\Models\Program;


class ProgramEducationalTest extends TestCase
{

    use RefreshDatabase;

    protected $departmentUser;
    protected $program;
    protected $peo;

    protected function setUp(): void
    {
        parent::setUp();

        // create a role
        Role::factory()->create(['id' => 3, 'name' => 'Department']);
        Role::factory()->create(['id' => 2, 'name' => 'Dean']);
        // create a program
        $this->program = Program::factory()->create();
        //  create user
        $this->departmentUser = User::factory()->create(['role_id' => '3']);
        $this->actingAs($this->departmentUser);
        //create a peo
        $this->peo = ProgramEducationalObjective::factory()->create();
    }

    // test if department user can retrieve all peos

    public function test_department_user_can_get_all_peos()
    {

        ProgramEducationalObjective::factory()->count(4)->create();

        $response = $this->getJson('api/v1/department/program-educational-objectives');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test if dept user can retrieve single peo

    public function test_department_user_can_get_peo()
    {
        $response = $this->get("api/v1/department/program-educational-objectives/{$this->peo->id}");

        $response->assertStatus(200)->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test if dept user can create peo

    public function test_department_user_can_create_peo()
    {

        $newPeo = ProgramEducationalObjective::factory()->make();

        $response = $this->postJson("api/v1/department/program-educational-objectives", $newPeo->toArray());

        $response->assertStatus(201);

        // check if the response return the expected json structure
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
        // check if atleast one attribute is returned in the response
        $response->assertJson([
            'data' => [
                'statement' => $newPeo->statement
            ]
        ]);

        // check if the newly created peo is successfully stored in the database

        $this->assertDatabaseHas(
            'program_educational_objectives',
            $newPeo->toArray(),
        );
    }

    // test if dept user can update existing peo

    public function test_department_user_can_update_peo()
    {
        $newPeo = ProgramEducationalObjective::factory()->make();

        $response = $this->putJson("api/v1/department/program-educational-objectives/{$this->peo->id}", $newPeo->toArray());

        // assert if the it successfully updated
        $response->assertStatus(200);

        // assert if the response has the data we expect (atleast 1)
        $response->assertJson([
            'data' => [
                'statement' => $newPeo->statement,
            ],
            'message' => 'PEO updated successfully',

        ]);

        // assert if the data successfully stored in the database 
        $this->assertDatabaseHas(
            'program_educational_objectives',
            $newPeo->toArray(),
        );
    }

    public function test_department_user_can_delete_peo()
    {
        // make a delete request 
        $response = $this->deleteJson("api/v1/department/program-educational-objectives/{$this->peo->id}");

        // assert if the deletion was successful,
        $response->assertStatus(200);

        // assert if the database has successfully removed the data that was deleted
        $this->assertDatabaseMissing(
            'program_educational_objectives',
            $this->peo->toArray(),
        );
    }

    // test if non dept user can access peo routes

    public function test_non_department_user_cannot_access_peos()
    {
        // create a user that is not a department role
        $newUser = User::factory()->create(['role_id' => 2]);
        $this->actingAs($newUser);

        // make a get request
        $response = $this->getJson('api/v1/department/program-educational-objectives');

        $response->assertStatus(403);
    }
}
