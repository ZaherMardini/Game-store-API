<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
  public function show(Product $product){
    return response()->json($product->load('reviews'));
  }
  public function store(Product $product, Request $request){
    $info = $request->validate([
      'product_id' => ['required', 'integer', 'exists:products,id'],
      'user_id' => ['required', 'integer', 'exists:users,id'],
      'body' => ['required', 'string']
    ]);
    Review::create($info);
    return response()->json($product->load('reviews'));
  }
}
