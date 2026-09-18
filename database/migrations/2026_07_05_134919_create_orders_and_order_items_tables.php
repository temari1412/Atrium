<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 注文テーブル
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // 購入者
            $table->integer('total_amount');
            $table->string('stripe_id')->nullable();
            
            // 追記　ステータス未送信・決済情報や配送先
            $table->string('status')->default('pending');
            $table->string('external_order_id')->nullable();
            $table->json('shipping_address')->nullable();
            
            $table->timestamps();
        });
    
        // 注文明細テーブル
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained();
            $table->string('variant_id')->nullable(); 
            $table->integer('quantity');
            $table->integer('price_at_purchase');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};