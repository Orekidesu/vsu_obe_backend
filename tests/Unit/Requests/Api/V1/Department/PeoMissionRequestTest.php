<?php

namespace Tests\Unit\Requests\Api\V1\Department;

use App\Models\Mission;
use Tests\TestCase;
use App\Models\Program;
use App\Models\ProgramEducationalObjective;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PeoMissionRequestTest extends TestCase
{
    /**
     * A basic unit test example.
     */
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

        $this->peo = ProgramEducationalObjective::factory()->create();
    }

    // test mission id should be required
    public function test_requires_mission_ids()
    {
        $response = $this->postJson("/api/v1/department/peo-mission/{$this->peo->id}/attach", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mission_ids']);
    }

    // test mission ids should be an array format
    public function test_requires_mission_ids_to_be_an_array()
    {
        $response = $this->postJson("/api/v1/department/peo-mission/{$this->peo->id}/attach", [
            'mission_ids' => 'not-an-array'
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors([
            'mission_ids'
        ]);
    }

    // test mission ids are valid and existent

    public function test_requires_valid_mission_ids()
    {
        $response = $this->postJson("/api/v1/department/peo-mission/{$this->peo->id}/attach", [
            'mission_ids' => [999], // Non-existent ID
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mission_ids.0']);
    }

    // test accepts valid mission ids
    public function test_allows_valid_mission_ids()
    {
        $missions = Mission::factory()->count(2)->create();


        $response = $this->postJson("/api/v1/department/peo-mission/{$this->peo->id}/attach", [
            'mission_ids' => $missions->pluck('id')->toArray(),
        ]);

        $response->assertStatus(200)->assertJson(['message' => 'Missions mapped to PEO successfully']);
    }
}
