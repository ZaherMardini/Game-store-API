<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    $products = [
      Product::factory()->create(['name' => 'Marvel spiderman', 'price' => 120]),
      Product::factory()->create(['name' => 'Legend of Zelda', 'price' => 50]),
      Product::factory()->create(['name' => 'Hollow Knight', 'price' => 45]),
      Product::factory()->create(['name' => 'Silk song', 'price' => 20]),
      Product::factory()->create(['name' => 'COD', 'price' => 120]),
      Product::factory()->create(['name' => 'Tekken 8', 'price' => 50]),
      Product::factory()->create(['name' => 'Outer wilds', 'price' => 45]),
      Product::factory()->create(['name' => 'Sifu', 'price' => 30]),
    ];
    $categories = ['All', 'RPG', 'Metroid vania', 'FPS', 'Adventures', 'Action', 'Open world', 'Strategy'];
    foreach ($categories as $category) {
      Category::create(['name' => $category]);
    };

    $products[0]->categories()->attach(5);
    $products[1]->categories()->attach(7);
    $products[1]->categories()->attach(5);
    $products[2]->categories()->attach(5);
    $products[3]->categories()->attach(1);
    $products[4]->categories()->attach(2);
    $products[5]->categories()->attach(6);
    $products[6]->categories()->attach(5);
    $products[7]->categories()->attach(3);
    $products[7]->categories()->attach(5);
  }
}
