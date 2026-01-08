<?php
namespace App\Services;

use App\Http\Requests\StoreCartItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartService{
  protected $request;
  protected $validInfo;
  protected $login;
  public $guest_token;

  public function setRequest(StoreCartItemRequest $request){
    $this->request = $request;
    $this->validInfo = $request->validated();
    $this->login = Auth::guard('sanctum');
    $this->guest_token = $request->cookie('guest_token');
  }
  public function isFirstVisit(){
    return !$this->login->check() && !isset($this->guest_token);
  }
  public function isConsecutive(){
    return !$this->login->check() && isset($this->guest_token);
  }
  public function isLoggedGuest(){
    return $this->login->check() && isset($this->guest_token);
  }
  public function firstVisit(){
    $info = $this->request->validated();
    $token = Str::uuid();
    $guest_cookie = cookie('guest_token', $token, '120');
    $cart = Cart::create(['user_id' => null, 'guest_token' => $token]);
    if(!isset($info['quantity'])){
      $info['quantity'] = 1;
    }
    $info['cart_id'] = $cart['id'];
    CartItem::create($info);
    $cart->load('items');
    return response()->json(['New cart created' => $cart])->cookie($guest_cookie);
  }
  public function insertItem($info){
    $item = 
    CartItem::where('cart_id', $info['cart_id'])
    ->where('product_id',$info['product_id'])->first();
    if(!isset($info['quantity'])){
      $info['quantity'] = 1;
    }
    if($item){
      $item['quantity'] += 1;
      $item->save();
    }
    else{
      $item = CartItem::create($info);
    }
  }
  public function tempToNormalCart(Cart $cart){
    $cart['guest_token'] = null;
    $cart['user_id'] = Auth::guard('sanctum')->id();
    $cart->save();
    return $cart;
  }
  public function insertToTempCart(Cart $tempCart){
    $info = $this->request->validated();
    $info['cart_id'] = $tempCart['id'];
    $this->insertItem($info);
    return $tempCart;
  }
  public function loggedGuest(int $id ,string $token){
    $temp_cart = Cart::where('guest_token', $token)->first();
    $cart = Cart::where('user_id', $id)->first();
    if($cart && $temp_cart){
      $this->insertToTempCart($temp_cart);
      $mergedSuccessfully = $this->mergeCarts($temp_cart, $cart);
      if($mergedSuccessfully){
        return $cart;
      }
    }
    else if(!$cart && $temp_cart){
      return $this->loggedGuestRequest($temp_cart);
    }
  }
  public function loggedUser(int $id){
    $cart = Cart::where('user_id', $id)->firstOrCreate(['user_id' => $id], ['user_id' => $id]);
    $info = $this->request->validated();
    $info['cart_id'] = $cart['id'];
    $this->insertItem($info);
    return $cart;
  }
  public function loggedGuestRequest(Cart $tempCart){
    $info = $this->request->validated();
    $cart = $this->tempToNormalCart($tempCart);
    if($cart){
      $info['cart_id'] = $cart['id'];
      Cookie::queue(Cookie::forget('guest_token'));
      $this->insertItem($info);
      return $cart;
    }
  }
  public function storeProtocol(){
    $cart = null;
    $responseMessage = 'success';
    if($this->isFirstVisit()){
      return $this->firstVisit();
    }
    else if($this->isConsecutive()){
      $tempCart = Cart::where('guest_token', $this->guest_token)->firstOrFail();
      $cart = $this->insertToTempCart($tempCart);
      $responseMessage = 'Consecutive add to cart';
    }
    else if($this->isLoggedGuest()){
      $cart = $this->loggedGuest($this->login->id(), $this->guest_token);
      $responseMessage = 'Guest user logged in';
    }
    else{
      $cart = $this->loggedUser($this->login->id());
      $responseMessage = 'Logged in user';
    }
    $cart->load('items');
    return response()->json([$responseMessage => $cart]);
  }
  public function mergeCarts(Cart $temp_cart, Cart $cart){
    $keyedItems = collect($cart->items)->keyBy('product_id');
    $tempItems = collect($temp_cart->items);
    $syncNew = [];
    $syncQuantity = [];
    foreach ($tempItems as $tempItem) {
      if($keyedItems->get($tempItem['product_id'])){
        $keyItem = $keyedItems->get($tempItem['product_id']);
        $keyItem['quantity'] += $tempItem['quantity'];
        $syncQuantity[] = $keyItem;
      }
      else{
        $tempItem['cart_id'] = $cart['id'];
        $syncNew[] = $tempItem;
      }
    }
    try {
      $this->commit($syncNew, $syncQuantity);
    } catch (\Throwable $th) {
      throw $th;
    }
    $temp_cart->delete();
    Cookie::queue(Cookie::forget('guest_token'));
    return true;
  }
  public function commit(array $toMove, array $toSyncQuantity){
    DB::transaction(function() use($toMove, $toSyncQuantity){
      if(!empty($toSyncQuantity)){
        $this->syncQuantity($toSyncQuantity);
      }
      if(!empty($toMove)){
        $this->syncNewProducts($toMove);
      }
    });
  }
  public function syncQuantity(array $items){
    $items = collect($items);
    $ids = $items->pluck('id');
    foreach ($items as $item) {
      DB::table('cart_items')
      ->whereIn('id', $ids)
      ->update([
        'quantity' => $item['quantity'],
        'updated_at' => now()
      ]);
    }
  }
  public function syncNewProducts(array $items){
    $items = collect($items);
    DB::table('cart_items')
    ->whereIn('id', collect($items)->pluck('id'))
    ->update([
      'cart_id' => $items[0]['cart_id'],
      'updated_at' => now(),
    ]);
  }
  public function clear(Cart $cart){
    $cart->items()->delete();
  }
  public function removeItem(CartItem $item){
    $item->delete();
  }
}