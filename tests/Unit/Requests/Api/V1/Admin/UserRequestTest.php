<?php

namespace Tests\Unit\Requests\Api\V1\Admin;

// use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Faculty;
use App\Models\Department;

class UserRequestTest extends TestCase
{
    /**
     * A basic unit test example.
     */

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setup();

        Role::factory()->create(['id' => 1, 'name' => 'Admin']);
        Faculty::factory()->create(['id' => 1]);
        Department::factory()->create(['id' => 1]);
    }

    public function test_it_requires_mandatory_fields()
    {
        // Acting as an admin user
        $admin = User::factory()->create(['role_id' => 1]);
        $this->actingAs($admin);

        // Send an empty request
        $response = $this->postJson('/api/v1/admin/users', []);


        $response->assertStatus(500);
    }

    public function test_it_allows_optional_fields_on_update()
    {
        $admin = User::factory()->create(['role_id' => 1]);
        $user = User::factory()->create();

        $this->actingAs($admin);

        // Send an update request with only first name
        $response = $this->patchJson("/api/v1/admin/users/{$user->id}", [
            'first_name' => 'Updated Name',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'first_name' => 'Updated Name',
        ]);
    }

    public function test_email_must_be_unique()
    {

        // create a user
        $existingUser = User::factory()->create(['email' => 'exist@gmail.com']);
        $admin = User::factory()->create(['role_id' => 1]);
        $this->actingAs($admin);


        $response = $this->postJson('/api/v1/admin/users', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'exist@gmail.com', // Duplicate email
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_id' => 1,
            'faculty_id' => 1,
            'department_id' => 1, //
        ]);


        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
