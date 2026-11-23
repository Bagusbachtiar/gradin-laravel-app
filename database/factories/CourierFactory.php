<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CourierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code'      => 'KUR-' . fake()->unique()->numerify('####'),
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'phone'     => '08' . fake()->unique()->numerify('##########'),
            'level'     => fake()->numberBetween(1, 5),
            'is_active' => true,
        ];
    }
}
