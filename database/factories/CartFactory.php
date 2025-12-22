<?php

namespace Database\Factories;
use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
  // protected $model = Cart::class;

  public function definition()
  {
    return [
      'user_id' => null,
      'guest_token' => null,
  ];
  }

  public function guest()
  {
      return $this->state(fn () => [
          'user_id' => null,
          'guest_token' => $this->faker->uuid,
      ]);
  }

  public function forUser($user)
  {
      return $this->state(fn () => [
          'user_id' => $user->id,
          'guest_token' => null,
      ]);
  }
}
