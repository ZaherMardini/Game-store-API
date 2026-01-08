<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
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
    return $this->service->storeProtocol();
  }
  public function clear(Cart $cart){
    $this->service->clear($cart);
    return response()->json('Items cleared');
  }
  public function removeItem(Cart $cart, CartItem $item){
    $this->service->removeItem($item);
    $cart->load('items');
    return response()->json(['Item removed' => $item, 'From Cart' => $cart]);
  }
}
