<?php

namespace Database\Factories;

use App\Models\BoardColumn;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'column_id' => BoardColumn::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'position' => 0,
        ];
    }
}