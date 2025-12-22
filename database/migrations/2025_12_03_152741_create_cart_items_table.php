<?php

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('cart_items', function (Blueprint $table) {
        $table->id();
        $table->foreignIdFor(Product::class);
        $table->foreignIdFor(Cart::class)->constrained()->cascadeOnDelete();
        $table->integer('quantity')->default(0);
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::dropIfExists('cart_items');
    }
};
