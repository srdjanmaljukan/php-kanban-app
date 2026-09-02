<?php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

class ColumnFactory extends Factory
{
    protected $model = \App\Models\BoardColumn::class;

    public function definition(): array
    {
        return [
            'board_id' => Board::factory(),
            'name' => $this->faker->word(),
            'position' => 0,
        ];
    }
}