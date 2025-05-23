<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory()->count(4)->create();
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role_id' => 1,
            // Admin role ID
            // Optional fields based on your requirements:
            'faculty_id' => null,
            'department_id' => null,
        ]);

        // Create 4 random users as before
        User::factory()->count(4)->create();
    }
}
