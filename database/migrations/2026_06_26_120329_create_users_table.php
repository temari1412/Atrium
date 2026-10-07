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
        Schema::create('users', function (Blueprint $table) { //テーブルを作成・upで実行
            $table->id();// ① ID（主キー・自動インクリメント）
            $table->string('name');// ② ユーザー名
            $table->string('email')->unique();// ③ メールアドレス（重複不可：unique）
            $table->string('password');// ④ パスワード（ハッシュ化されて保存される）
            $table->date('birth_date')->nullable();// ⑤ 生年月日（年代計算やプロフィール表示で使用・空欄可能）
            $table->string('gender')->nullable();// ⑥ 性別（グラフ分析やプロフィール用・空欄可能）
            
            $table->string('icon_image')->nullable();// ⑦ アイコン画像のパス（またはURL）
            $table->string('header_image')->nullable();// ⑧ ヘッダー画像のパス（またはURL）
            
            $table->string('introduction')->nullable();// ⑨ 自己紹介文（項目14の「プロフィールの自己紹介」で使用するカラム）
            $table->text('crop_settings')->nullable();// ⑩ 画像切り抜き設定（CropperJSなどの座標データ等を保持）
            $table->tinyInteger('is_admin')->default(0);// ⑪ 管理者フラグ（0: 一般ユーザー, 1: 管理者。項目1の権限制御で使用）
            $table->boolean('is_featured')->default(false);// ⑫ おすすめ・特集ユーザーフラグ（true / false）
            $table->rememberToken();// ⑬ ログイン状態を保持するトークン（「ログインしたままにする」用）
            $table->timestamps();// ⑭ 作成日時(created_at) と 更新日時(updated_at) を自動管理
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};