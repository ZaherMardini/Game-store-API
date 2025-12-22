<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

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
    $guest_token = $request->cookie('guest_token');
    $login = Auth::guard('sanctum');
    if(!$login->check() && !isset($guest_token)){
      return $this->service->firstVisit();
    }
    else if(!$login->check() && isset($guest_token)){
      return $this->service->subsequent($guest_token);
    }
    else if($login->check() && isset($guest_token)){
      return $this->service->loggedGuest($login->id(), $guest_token);
    }
    else{
      return $this->service->loggedUser($login->id());
    }
  }
}
