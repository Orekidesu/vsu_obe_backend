<?php

namespace Tests\Feature\Api\V1\Department;

use App\Models\ProgramOutcome;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Program;
use Laravel\Sanctum\Sanctum;

class ProgramOutcomeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    protected $departmentUser;
    protected $po;
    protected $program;
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        // Create Role Dean and Department
        Role::factory()->create(['id' => 2, 'name' => 'Dean']);
        Role::factory()->create(['id' => 3, 'name' => 'Department']);
        // Create program
        $this->program = Program::factory()->create();

        // Create User
        $this->departmentUser = User::factory()->create(['role_id' => 3]);
        $this->actingAs($this->departmentUser);
        // Create PO

        $this->po = ProgramOutcome::factory()->create();
    }

    // test dept user can get all pos
    public function test_dept_user_can_get_all_pos()
    {
        ProgramOutcome::factory()->count(5)->create();
        $response = $this->getJson('/api/v1/department/program-outcomes');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test dept user can get single po
    public function test_dept_user_can_get_single_po()
    {

        // make a get request to get request
        $response = $this->getJson("/api/v1/department/program-outcomes/{$this->po->id}");

        $response->assertStatus(200)->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test dept user can create po
    public function test_dept_user_can_create_po()
    {
        $newPo = ProgramOutcome::factory()->make();

        $response = $this->postJson("/api/v1/department/program-outcomes", $newPo->toArray());

        $response->assertStatus(201);

        $response->assertJson([
            'data' => [
                'name' => $newPo->name,
                'statement' => $newPo->statement,
            ]
        ]);


        $this->assertDatabaseHas('program_outcomes', $newPo->toArray());
    }

    // test dept user can update po
    public function test_dept_user_can_update_po()
    {
        $updateData  = [
            'name' => 'Program Outcome something',
            'statement' => 'some statement'
        ];

        $response = $this->putJson("/api/v1/department/program-outcomes/{$this->po->id}", $updateData);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'name' => $updateData['name'],
                'statement' => $updateData['statement'],

            ],
            'message' => 'PO updated successfully'
        ]);

        $this->assertDatabaseHas('program_outcomes', $updateData);
    }

    // test dept user can delete po
    public function test_dept_user_can_delete_po()
    {
        // initialize a new peo to the database
        $newPo = ProgramOutcome::factory()->create();

        // make a delete request
        $response = $this->deleteJson("/api/v1/department/program-outcomes/{$newPo->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('program_outcomes', $newPo->toArray());
    }

    // test non-dept user can access po
    public function test_non_dept_user_can_access_po()
    {
        // create a non department user

        $newUser = User::factory()->create(['role_id' => 2]);
        Sanctum::actingAs($newUser);

        //make a get request using the non deparmtent user
        $response = $this->getJson('/api/v1/department/program-outcomes');

        $response->assertStatus(403);
    }
}
