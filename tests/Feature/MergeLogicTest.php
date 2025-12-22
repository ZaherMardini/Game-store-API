<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MergeLogicTest extends TestCase
{
  use RefreshDatabase;
  public function test_guest_cart_is_merged_into_user_cart_on_login()
  {
    $product_1 = Product::factory()->create();
    $product_2 = Product::factory()->create();

    $temp_cart = Cart::create([
      'user_id' => null,
      'guest_token' => 'token'
    ]);

    $user = User::factory()->create();

    $guestCart = Cart::factory()->guest()->create();
    $userCart  = Cart::factory()->forUser($user)->create();

    CartItem::factory()->create([
      'cart_id' => $guestCart->id,
      'product_id' => $product_1->id,
      'quantity' => 2,
    ]);

    CartItem::factory()->create([
      'cart_id' => $guestCart->id,
      'product_id' => $product_2->id,
      'quantity' => 1,
    ]);

    CartItem::factory()->create([
    'cart_id' => $userCart->id,
    'product_id' => $product_1->id,
    'quantity' => 3,
    ]);
    

    // Merge logic call 
    // dd($user, get_class($user));

    $this->actingAs($user, 'sanctum');
    app(\App\Services\CartService::class)
    ->mergeCarts($guestCart, $userCart);


    $this->assertDatabaseHas('cart_items', [
    'cart_id' => $userCart->id,
    'product_id' => $product_1->id,
    'quantity' => 5,
    ]);

    $this->assertDatabaseHas('cart_items', [
    'cart_id' => $userCart->id,
    'product_id' => $product_2->id,
    'quantity' => 1,
    ]);

    $this->assertEquals( // no duplicates
    2,
    CartItem::where('cart_id', $userCart->id)->count()
    );

    $this->assertDatabaseMissing('carts', [
      'id' => $guestCart->id,
    ]);
  }
}
