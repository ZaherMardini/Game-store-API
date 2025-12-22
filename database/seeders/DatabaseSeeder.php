<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
          'name' => 'Legend',
          'email' => 'legend@legend.com',
          'sudo' => true,
          // token: 4|pQKRwcqjs9BgsegfHPzNMl1eS4SO4VaBxumAnVwE9bb89b19
        ]);
        User::factory()->create([
          'name' => 'object',
          'email' => 'object@object.com',
          'sudo' => false,
          // token: 2|zfec4eAVWZs7dmQLx7aWyZgmHwa9vj3hWHlLEbrCbc534e04
        ]);
        User::factory()->create([
          'name' => 'test',
          'email' => 'test@test.com',
          'sudo' => false,
          // token: 3|sfzbI6ioiP5qLZEgumBTkLB7dfr7ZNxU2DgYFcsJ8a6c2d37
        ]);
      $this->call(ProductSeeder::class);
    }
}
