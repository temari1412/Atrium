<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // === 1. 運営おすすめクリエイターたちの作成 ===
        
        // クリエイター1：鮫島ぬりえ
        $creator1 = User::updateOrCreate(
            ['email' => 'nurie@example.com'],
            [
                'name' => '鮫島ぬりえ',
                'password' => Hash::make('password'),
                'icon_image' => '73D1DAFB-24AF-4CA3-AC83-A2E42DB80961.jpg',          // アイコン画像
                'header_image' => '5AB6FA6E-241F-42F2-B746-6462CEA8415E.jpg', // ヘッダー背景画像
                'is_featured' => true,                // ★運営おすすめに設定！
            ]
        );

        // クリエイター2：てまり
        $creator2 = User::updateOrCreate(
            ['email' => 'temarucos@gmail.com'],
            [
                'name' => 'てまり',
                'password' => Hash::make('4869467080w'),
                'icon_image' => '90528723_p1_master1200.jpg',
                'header_image' => '90528723_p2_master1200.jpg',
                'is_featured' => true,                // ★運営おすすめに設定！
            ]
        );

        // クリエイター3：ふわふわしっぽ
        $creator3 = User::updateOrCreate(
            ['email' => 'nekomaru@example.com'],
            [
                'name' => 'Chloe',
                'password' => Hash::make('password'),
                'icon_image' => 'icon1.jpg',
                'header_image' => '90704551_p0_square1200.jpg',
                'is_featured' => true,                // ★運営おすすめに設定！
            ]
        );


        // === 2. 商品（グッズ）データの作成 ===
        
        // 鮫島ぬりえさんの商品
        Product::create([
            'user_id' => $creator1->id,
            'name' => '『罰して光』ポストカード',
            'price' => 2200,
            'description' => 'なきそさんのMVのポストカード。白ベースのクリーンな額縁にも映えるデザインです。',
            'category' => 'ポストカード',
            'image' => 'punish-light.jpeg',
            'status' => 'published',
        ]);

        // sumiさんの商品（ランキングテスト用などに適当にいいね数を想定）
        Product::create([
            'user_id' => $creator2->id,
            'name' => 'フォウくんアクキー',
            'price' => 850,
            'description' => '透明感のあるアクリルキーホルダー。光に透かすととても綺麗です。',
            'category' => 'キーホルダー',
            'image' => '90528723_p1_master1200.jpg', // 前に用意したテスト画像など
            'status' => 'published',
        ]);
    }
}