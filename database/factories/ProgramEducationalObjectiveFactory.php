<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProgramEducationalObjective>
 */
class ProgramEducationalObjectiveFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'peo_no' => $this->faker->unique()->numberBetween(1, 20),
            'statement' => $this->faker->paragraph(),
            'program_id' => \App\Models\Program::factory(),
        ];
    }
}
