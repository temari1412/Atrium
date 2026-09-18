<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\User;
use App\Models\Tag;
use Carbon\Carbon;

class TopPageController extends Controller
{
    public function index()
    {
        // 1. おすすめアイテム
        $recommendedProducts = Product::with(['user', 'tags'])
                    ->withCount('likes')
                    ->whereIn('status', ['public', 'published'])
                    ->orderBy('created_at', 'desc')
                    ->take(6)
                    ->get();

        // 2. 人気ランキング
        $rankingProducts = Product::with(['user', 'tags'])
                    ->withCount('likes')
                    ->whereIn('status', ['public', 'published'])
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(6)
                    ->get();

        // 3. 今月のトレンドタグ（安全に取得）
        $now = Carbon::now();
        $hashtags = collect();

        try {
            $hashtags = Tag::whereHas('products', function ($query) use ($now) {
                            $query->whereIn('status', ['public', 'published'])
                                  ->whereYear('products.created_at', $now->year)
                                  ->whereMonth('products.created_at', $now->month);
                        })
                        ->withCount(['products' => function ($query) use ($now) {
                            $query->whereIn('status', ['public', 'published'])
                                  ->whereYear('products.created_at', $now->year)
                                  ->whereMonth('products.created_at', $now->month);
                        }])
                        ->orderByDesc('products_count')
                        ->take(4)
                        ->get()
                        ->pluck('name');
        } catch (\Exception $e) {
            // エラー時はスキップ
        }

        // フォールバック
        if ($hashtags->isEmpty()) {
            $hashtags = Tag::take(4)->pluck('name');
            if ($hashtags->isEmpty()) {
                $hashtags = collect(['ポストカード', 'キーホルダー', 'ステッカー', 'アクリルグッズ']);
            }
        }

        // 4. 運営おすすめユーザー（出品数が多いクリエイター上位3人を安全に自動取得）
        $featuredCreators = User::withCount('products')
                    ->where('is_admin', 0) // 管理者を除外
                    ->orderByDesc('products_count')
                    ->take(3)
                    ->get();
   
        return view('top', compact('recommendedProducts', 'rankingProducts', 'hashtags', 'featuredCreators'));
    }
}