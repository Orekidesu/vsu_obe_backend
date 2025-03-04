<?php

namespace Tests\Feature\Api\V1\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Faculty;
use App\Models\Department;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected function setup(): void
    {
        parent::setup();
        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Role::factory()->create(['id' => 2, 'name' => 'Dean']);
        Faculty::factory()->create(['id' => 1]);
        Department::factory()->create(['id' => 1]);
    }
    public function test_admin_can_list_all_users()
    {
        Sanctum::actingAs(User::factory()->create(['role_id' => 1]));

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(200)->assertJsonStructure(['data', 'message',]);
    }

    public function test_non_admin_cannot_access_users_list()
    {
        Sanctum::actingAs(User::factory()->create(['role_id' => 2]));

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user()
    {
        Sanctum::actingAs(User::factory()->create(['role_id' => 1]));
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 1,
            'faculty_id' => 1,
            'department_id' => 1

        ];
        $response = $this->postJson('/api/v1/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJson(['message' => 'user created successfully']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_admin_can_delete_user()
    {
        Sanctum::actingAs(User::factory()->create(['role_id' => 1]));

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");
        // dd($response);

        $response->assertStatus(200)->assertJson(['message' => "user deleted successfully"]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
