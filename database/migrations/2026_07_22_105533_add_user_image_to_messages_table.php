<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // 購入者のアイコン画像パス（またはURL）を保存するカラム
            $table->string('user_image')->nullable()->after('body');
        });
    }
    
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('user_image');
        });
    }
};
