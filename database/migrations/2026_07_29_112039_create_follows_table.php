<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // フォローする側
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade'); // フォローされる側
            $table->timestamps();
    
            // 同じ人を二重にフォローしないようにユニーク制約をつける
            $table->unique(['user_id', 'following_id']);
        });
    }
};
