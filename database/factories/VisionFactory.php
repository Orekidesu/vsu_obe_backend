<?php

namespace Database\Factories;

use App\Models\Vision;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisionFactory extends Factory
{
    protected $model = Vision::class;

    public function definition()
    {
        return [
            'description' => $this->faker->paragraph,
        ];
    }
}
