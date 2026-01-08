<?php
namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService {
  protected $cart;
  protected $order_items = [];
  protected $login;
  protected $order;
  protected $request;

  public function getOrder(): Order{
    return $this->order;
  }
  public function setOrder(Order $order){
    $this->order = $order;
  }
  public function setRequest(Request $request){
    $this->request = $request;
  }
  public function initialize(){
    $this->login = Auth::guard('sanctum');
    $user = $this->login->user();
    $this->cart = $user->cart;
    $this->cart->load('items.product');
  }
  public function checkout(){
    if(!isset($this->cart) || empty($this->cart->items->toArray()) || !$this->login->check() || !$this->validItems()){
      return false;
    }
    try {
      DB::transaction(function(){
        $order = $this->newOrder();
        if(isset($order)){
          foreach ($this->cart->items as $item) {
            $this->order_items[] = $this->cartToOrderItem($item, $order); 
          }
        }
        $order->update([
        'totalAmount' => $this->getTotalAmount(),
        'status' => 'new',
        'idempotency_key' => $this->request->header('idempotency-key')
        ]);
        $this->order = $order;
        $this->cart->items()->delete();
      });
      return $this->paymentComplete();
    }
    catch (\Throwable $th) {
      throw $th;
    }
  }
  public function paymentComplete(){// mocking payment integration with $paid variable
    $paid = true;
    if($this->order['status'] === 'new' && $paid){
      $this->order->update(['status' => 'paid', 'idempotency_key' => 'paid_key']);
      return true;
    }
    return false;
  }
  public function cancel(){
    if($this->order['status'] === 'pending' || $this->order['status'] === 'new'){
      $this->order->update(['status' => 'canceled', 'idempotency_key' => 'canceled_key']);
      return true;
    }
    return false;
  }
  public function refund(){
    if($this->order['status'] === 'paid'){
      $this->order->update(['status' => 'refunded', 'idempotency_key' => 'refunded_key']);
      return true;
    }
    return false;
  }
  private function validItems(){
    $items = $this->cart->items;
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
  private function newOrder(){
    $orderInfo = [
      'user_id' => $this->login->id(),
      'status' => 'pending',
      'totalAmount' => 0.0,
      'idempotency_key' => null
    ];
    return Order::create($orderInfo);
  }
  private function cartToOrderItem(CartItem $cItem, Order $order){
    $oItemInfo = [];
    $oItemInfo['product_id'] = $cItem['product_id'];
    $oItemInfo['quantity'] = $cItem['quantity'];
    $oItemInfo['order_id'] = $order['id'];
    $oItemInfo['price_when_purchased'] = $cItem->product->price;
    return OrderItem::create($oItemInfo);
  }
  private function getTotalAmount(): float{
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
  public function storeOrder(){
    $request = $this->request;
    $key = $request->header('idempotency-key');
    $order = Order::where('idempotency_key', $key)->first();
    if($order){
      return response()->json(['Order exists' => $order]);
    }
    $result = $this->checkout();
    if($result){
      $order = $this->getOrder();
      $order['idempotency_key'] = $key;
      return response()->json(['New order created' => $order]);
    }
    return response()->json('Checkout failed, check the "checkout flow" docs');
  }
}