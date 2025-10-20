<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->words(2, true); // contoh: "Jaket Hoodie"
        return [
            'name'        => ucfirst($name),
            'slug'        => Str::slug($name . '-' . fake()->unique()->numberBetween(100,999)),
            'description' => fake()->paragraph(),
            'price'       => fake()->numberBetween(25000, 350000),
            'weight'      => fake()->numberBetween(100, 1500), // gram
            'image_url'   => 'https://picsum.photos/seed/'.fake()->uuid().'/600/400',
        ];
    }
}
