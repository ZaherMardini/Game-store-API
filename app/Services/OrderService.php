<?php
namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService {
  protected $cart;
  protected $cart_items;
  protected $order_items = [];
  protected $login;
  protected $order;

  public function getOrder(){
    return $this->order;
  }
  public function setCart(Cart $cart){
    $this->login = Auth::guard('sanctum');
    $this->cart = $cart->load('items.product');
    $this->cart_items = $cart->items;
  }
  public function checkout(){
    if(!isset($this->cart) || !$this->login->check() || !$this->validItems()){
      return;
    }
    try {
      DB::transaction(function(){
        $order = $this->newOrder();
        if(isset($order)){
          foreach ($this->cart_items as $item) {
            $this->order_items[] = $this->cartToOrderItem($item, $order); 
          }
        }
        $order->update([
        'totalAmount' => $this->getTotalAmount(),
        'status' => 'new'
        ]);
        $this->order = $order;
        $this->cart->items->delete();
      });
    }
    catch (\Throwable $th) {
      throw $th;
    }
  }
  public function cancle(int $id){
    $order = Order::find($id);
    if($order['status'] === 'pending' || $order['status'] === 'new'){
      $order->update(['status' => 'cancled']);
      return true;
    }
    return false;
  }
  public function refund(int $id){
    $order = Order::find($id);
    if($order['status'] === 'paid'){
      $order->update(['status' => 'refunded']);
      return true;
    }
    return false;
  }
  public function validItems(){
    $items = $this->cart_items;
    foreach ($items as $item) {
      if(!$item->product){
        return false;
      }
      if($item->quantity <= 0){
        return false;
      }
    }
    return true;
  }
  public function newOrder(){
    $orderInfo = [
      'user_id' => $this->login->id(),
      'status' => 'pending',
      'totalAmount' => 0.0,
      'idempotency_key' => null
    ];
    return Order::create($orderInfo);
  }
  public function cartToOrderItem(CartItem $cItem, Order $order){
    $oItemInfo = [];
    $oItemInfo['product_id'] = $cItem['product_id'];
    $oItemInfo['quantity'] = $cItem['quantity'];
    $oItemInfo['order_id'] = $order['id'];
    $oItemInfo['price_when_purchased'] = $cItem->product->price;
    return OrderItem::create($oItemInfo);
  }
  public function getTotalAmount(): float{
    $result = 0.0;
    if(!$this->order_items){
      return $result;
    }
    $items = $this->order_items;
    foreach ($items as $item) {
      $price = $item['price_when_purchased'];
      $result += $price * $item['quantity'];
    }
    return $result;
  }
}