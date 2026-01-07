<?php
namespace App\Services;

use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class ProductService{
  protected $request;
  protected $validInfo;
  protected $login;
  public function setRequest(StoreProductRequest $request){
    $this->request = $request;
    $this->validInfo = $request->validated();
    $this->login = Auth::guard('sanctum');
  }
  public function store(){
    $request = $this->request;
    $productInfo = $this->validInfo;
    $productProps = collect($productInfo)->except('categories')->all();
    $product = Product::where('name', $productProps['name'])->first();
    if(!$product){
      if($request->file('image')){
        $productProps['image_path'] = $request->file('image')->store('products_images', 'public');
        unset($productProps['image']);
      }
      $product = Product::create($productProps);
      if(collect($productInfo)->has('categories')){
        $product->categories()->sync($productInfo['categories']);
      }
    }
    $product = new ProductResource($product->load('categories'));
    return $product;
  }

  public function update(Product $product){
    $request = $this->request;
    $updatedInfo = $this->validInfo;
    $productInfo = collect($updatedInfo)->except('categories')->all();
    if($request->hasFile('image')){
      $productInfo['image_path'] = $request->file('image')->store('products_images', 'public');
      unset($productInfo['image']);
    }
    $product->update($productInfo);
    if (isset($updatedInfo['categories'])) {
      $product->categories()->sync($updatedInfo['categories']);
    }
    return new ProductResource($product->load('categories'));
  }
}