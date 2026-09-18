<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // これが重要
            $table->string('name');
            $table->integer('price');
            $table->string('category');
            $table->string('size')->nullable(); 
            $table->string('variant_id')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->string('status')->default('draft');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
