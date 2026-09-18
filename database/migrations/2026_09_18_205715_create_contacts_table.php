<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // ログインユーザーID（未ログインならnull）
            $table->string('name');       // お名前
            $table->string('email');      // メールアドレス
            $table->string('subject');    // 件名
            $table->text('message');      // お問い合わせ内容
            $table->string('status')->default('unanswered'); // ステータス (unanswered: 未対応, answered: 対応済み など)
            $table->timestamps();
        });
    }
};
