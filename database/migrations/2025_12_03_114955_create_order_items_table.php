<?php

use App\Models\Order;
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
      Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        $table->foreignIdFor(Product::class);
        $table->foreignIdFor(Order::class);
        $table->integer('quantity');
        $table->decimal('price_when_purchased', 10, 2);
        $table->timestamps();
      });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
