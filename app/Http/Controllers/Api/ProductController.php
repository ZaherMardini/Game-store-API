<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
  protected $service;
  public function __construct(ProductService $service){
    $this->service = $service;
  }
  public function index()
  {
    $products = Product::with('categories')->get();
    return ProductResource::collection($products);
  }

  public function store(StoreProductRequest $request)
  {
    $this->service->setRequest($request);
    $resource = $this->service->store();
    return $resource;
  }
  
  public function show(Product $product)
  {
    return new ProductResource($product->load('categories'));
  }
  
  public function update(StoreProductRequest $request, Product $product)
  {
    $this->service->setRequest($request);
    return $this->service->update($product);
  }

  public function destroy(string $id)
  {
    if(Gate::denies('Super user')){
      abort(403);
    }
    Product::findOrFail($id)->delete();
    return response()->json(['Deleted successfully']);
  }
}
