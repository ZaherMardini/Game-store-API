<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

Route::middleware('guest')->prefix('v1')->group(function(){
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);
  Route::get('/user', [AuthController::class, 'user']);
  // products
  Route::get('/products', [ProductController::class, 'index']);
  Route::get('/products/{product}', [ProductController::class, 'show']);
  // end products
  //guest cart
  Route::post('/cart/newItem', [CartController::class, 'store'])->middleware(AddQueuedCookiesToResponse::class);
  Route::get('/carts', [CartController::class, 'index']);
  Route::get('/cart', [CartController::class, 'show']);
  Route::delete('/cart/{cart}/items', [CartController::class, 'clear']);
  //end guest cart
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function(){
  Route::post('/logout', [AuthController::class, 'logout']);
  //orders
  Route::get('/orders', [OrderController::class, 'index']);
  Route::get('/orders/{order}', [OrderController::class, 'show']);
  Route::post('/orders', [OrderController::class, 'store']);
  //end orders
  // products
  Route::post('/products', [ProductController::class, 'store']);
  Route::patch('/products/{product}', [ProductController::class, 'update']);// document this
  Route::delete('/products/{product}', [ProductController::class, 'destroy']);
  // end products
  // Reviews
  Route::get('/products/{product}/reviews', [ReviewController::class, 'show']);
  Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);
  // end Reviews
});