<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => fake()->words(3, true),
      'price' => fake()->randomFloat(2, 10, 500), // Generates a random float with 2 decimals, between 10 and 500
      'description' => fake()->paragraph(5)
    ];
  }
}
