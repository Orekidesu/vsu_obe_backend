<?php

namespace Tests\Feature\Api\V1\Department;

use App\Models\Mission;
use App\Models\ProgramEducationalObjective;
use App\Models\Role;
use App\Models\User;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PeoMissionTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;
    protected $departmentUser;
    protected $peo;
    protected $program;
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

        $this->peo = ProgramEducationalObjective::factory()->create();
    }

    // test can fetch all PEOs with moissions
    public function test_dept_user_can_fetch_all_peos_with_missions()
    {
        ProgramEducationalObjective::factory()->count(3)->create();

        $response = $this->getJson("/api/v1/department/peo-mission");
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test dept user can get missions from specific peo
    public function test_dept_user_can_fetch_missions_from_specific_peo()
    {
        $missions = Mission::factory()->count(2)->create();
        $this->peo->missions()->attach($missions->pluck('id'));

        $response = $this->getJson("/api/v1/department/peo-mission/{$this->peo->id}");

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data',
            'message',
        ]);
    }

    // test non department user can't access peo-mission route
    public function test_non_dept_user_cannot_access_peo_mission_route()
    {
        //set non department user
        $newUser = User::factory()->create(['role_id' => 2]);
        Sanctum::actingAs($newUser);

        $response = $this->getJson("/api/v1/department/peo-mission");

        $response->assertStatus(403);
    }
}
