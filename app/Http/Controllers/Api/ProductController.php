<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      $products = Product::with('categories')->get();
      return response()->json(['products' => ProductResource::collection($products)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
      $info = $request->validated();
      $productProps = collect($info)->except('categories')->all();
      $product = Product::create($productProps);
      if(collect($info)->has('categories') && isset($product)){
        $product->categories()->sync($info['categories']);
      }
      return new ProductResource($product->load('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
      return new ProductResource($product->load('categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, Product $product)
    {
      $info = $request->validated(); // validation bug: StoreProductRequest fucking file doesn't work
      $productInfo = collect($info)->except('categories')->all();
      $product->update($productInfo);
      if (isset($info['categories'])) {
        $product->categories()->sync($info['categories']);
      }
      return new ProductResource($product->load('categories'));
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
