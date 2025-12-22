<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
  protected $service;
  public function __construct(OrderService $service)
  {
    $this->service = $service;
  }
  public function index(){
    return response()->json(Order::get());
  }
  public function show(Order $order){
    return response()->json($order);
  }
  public function store(Request $request){
    $key = $request->header('idempotency-key');
    $order = Order::where('idempotency_key', $key)->first();
    if($order){
      return response()->json(['Order' => $order]);
    }
    $this->service->checkout();
    $order = $this->service->getOrder();
    $order['idempotency_key'] = $key;
    return response()->json(['New order' => 'No order created']);
  }
}
