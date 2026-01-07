<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Cart;
use App\Services\CartService;

class CartController extends Controller
{
  protected $service;
  public function __construct(CartService $service){
    $this->service = $service;
  }
  public function index(){
    $carts = Cart::get();
    return response()->json(['carts' => $carts]);
  }
  public function store(StoreCartItemRequest $request){
    $this->service->setRequest($request);
    return $this->service->determineStoreFlow();
  }
  public function clear(Cart $cart){
    $cart->items()->delete();
    return response()->json('Items cleared');
  }
}
