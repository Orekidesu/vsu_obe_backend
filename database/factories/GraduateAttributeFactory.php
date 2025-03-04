<?php


namespace Database\Factories;

use App\Models\GraduateAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class GraduateAttributeFactory extends Factory
{
    protected $model = GraduateAttribute::class;

    public function definition()
    {
        return [
            'ga_no' => $this->faker->unique()->numberBetween(1, 20),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
        ];
    }
}
