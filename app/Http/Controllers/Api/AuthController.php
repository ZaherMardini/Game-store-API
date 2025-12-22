<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;


use Illuminate\Http\Request;

class AuthController extends Controller
{
  protected $service;
  protected $Auth;
  public function __construct(CartService $service)
  {
    $this->Auth = Auth::guard('sanctum');
    $this->service = $service;
  }
  public function register(Request $request){
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:5|confirmed',
    ]);
    if($validator->fails()){
      return response()->json(['errors' => $validator->errors(), 'status' => 'error'],422);
    }
    User::create($validator->validated());
    return response()->json(['message' => 'Registered Successfully'], 201);
  }

  public function login(Request $request){
    $validator = Validator::make($request->all(),[
      'email' => 'required|string|email',
      'password' => 'required|string',
    ]);
    // First check for the format of the info
    if($validator->fails()){
      return response()->json(['error' => $validator->errors()], 422);
    }
    // Now we know the info is well-formatted, we need to ensure that you're registered
    if(!Auth::attempt($request->only(['email', 'password']))){
      return response()->json(['error' => 'Wrong info'], 401);
    }
    $user = $this->Auth->user();
    $token = $user->createToken('token')->plainTextToken;
    return response()->json(['token' => $token, 'token_type' => 'Bearer']);
  }

  public function logout(Request $request)
  {
    $request->user()->currentAccessToken()->delete();
    return response()->json(['message' => 'Logged out']);
  }

  public function user(){
    if($this->Auth->check()){
      return response()->json($this->Auth->user());
    }
    return response()->json("Guest mode");
  }
}
