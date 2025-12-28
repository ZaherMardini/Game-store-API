<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
  protected $service;
  public function __construct(OrderService $service)
  {
    $this->service = $service;
    $this->service->initializing();
  }
  public function index(){
    return response()->json(Order::where('user_id', Auth::guard('sanctum')->id())->get());
  }
  public function show(Order $order){
    return response()->json($order);
  }
  public function store(Request $request){
    $this->service->setRequest($request);
    $key = $request->header('idempotency-key');
    $order = Order::where('idempotency_key', $key)->first();
    if($order){
      return response()->json(['Order exists' => $order]);
    }
    $result = $this->service->checkout();
    if($result){
      $order = $this->service->getOrder();
      $order['idempotency_key'] = $key;
      return response()->json(['New order created' => $order]);
    }
    return response()->json('Checkout failed, check the "checkout flow" docs');
  }

  public function cancel(Order $order){
    $this->service->setOrder($order);
    $result = $this->service->cancel();
    if($result){
      return response()->json(['Canceled' => $this->service->getOrder()]);
    }
    return response()->json(['Not Canceled']);
  }
  public function refund(Order $order){
    $this->service->setOrder($order);
    $result = $this->service->refund();
    if($result){
      return response()->json(['refunded' => $this->service->getOrder()]);
    }
    return response()->json(['Not refunded']);
  }
}
