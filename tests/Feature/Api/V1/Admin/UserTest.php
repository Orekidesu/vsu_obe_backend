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

    protected $admin;

    protected function setup(): void
    {
        parent::setup();


        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Role::factory()->create(['id' => 2, 'name' => 'Dean']);
        Faculty::factory()->create(['id' => 1]);
        Department::factory()->create(['id' => 1]);
        $this->admin = User::factory()->create(['role_id' => 1]);
        Sanctum::actingAs($this->admin);
    }
    public function test_admin_can_list_all_users()
    {

        User::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(200)->assertJsonStructure(['data', 'message',]);
    }


    public function test_admin_can_create_user()
    {
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

    public function test_admin_can_update_user()
    {


        $existingUser = User::factory()->create();
        $updateData = [
            "first_name" => "Edward",
            "last_name" =>  "Newgate",
        ];

        $response = $this->patchJson("/api/v1/admin/users/{$existingUser->id}", $updateData);


        $response->assertStatus(200)->assertJson(['data' => [
            'first_name' => 'Edward',
            'last_name' => 'Newgate',
        ]]);
    }

    public function test_admin_can_delete_user()
    {

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");
        // dd($response);

        $response->assertStatus(200)->assertJson(['message' => "user deleted successfully"]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_can_reset_user_password()
    {
        $existingUser = User::factory()->create();

        $response = $this->postJson("/api/v1/admin/users/{$existingUser->id}/reset-password");

        $response->assertStatus(200);
    }


    public function test_non_admin_cannot_access_users_list()
    {
        $user = User::factory()->create(['role_id' => 2]); // Assuming 2 is a regular user

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }
}
