<?php

namespace Database\Factories;

use App\Models\Mission;
use Illuminate\Database\Eloquent\Factories\Factory;

class MissionFactory extends Factory
{
    protected $model = Mission::class;

    public function definition()
    {
        return [
            'mission_no' => $this->faker->unique()->numerify('M###'),
            'description' => $this->faker->paragraph,
        ];
    }
}
