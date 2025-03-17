<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Faculty;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Factory>
 */
class FacultyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Faculty::class;
    public function definition(): array
    {
        return [
            //

            'name' => $this->faker->unique()->company(),
            'abbreviation' => strtoupper($this->faker->unique()->lexify('???'))
        ];
    }
}
