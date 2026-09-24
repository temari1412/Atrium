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
        // 1. おすすめアイテム（凍結ユーザーのグッズを除外）
        $recommendedProducts = Product::with(['user', 'tags'])
                    ->withCount('likes')
                    ->whereIn('status', ['public', 'published'])
                    ->whereHas('user', function ($query) {
                        $query->where('is_suspended', false);
                    })
                    ->orderBy('created_at', 'desc')
                    ->take(6)
                    ->get();

        // 2. 人気ランキング（凍結ユーザーのグッズを除外）
        $rankingProducts = Product::with(['user', 'tags'])
                    ->withCount('likes')
                    ->whereIn('status', ['public', 'published'])
                    ->whereHas('user', function ($query) {
                        $query->where('is_suspended', false);
                    })
                    ->orderBy('likes_count', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->take(6)
                    ->get();

        // 3. 今月のトレンドタグ（凍結ユーザーのグッズを除外して安全に取得）
        $now = Carbon::now();
        $hashtags = collect();

        try {
            $hashtags = Tag::whereHas('products', function ($query) use ($now) {
                            $query->whereIn('status', ['public', 'published'])
                                  ->whereYear('products.created_at', $now->year)
                                  ->whereMonth('products.created_at', $now->month)
                                  ->whereHas('user', function ($q) {
                                      $q->where('is_suspended', false);
                                  });
                        })
                        ->withCount(['products' => function ($query) use ($now) {
                            $query->whereIn('status', ['public', 'published'])
                                  ->whereYear('products.created_at', $now->year)
                                  ->whereMonth('products.created_at', $now->month)
                                  ->whereHas('user', function ($q) {
                                      $q->where('is_suspended', false);
                                  });
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

        // 4. 運営おすすめユーザー（管理者と凍結ユーザーを除外）
        $featuredCreators = User::withCount('products')
                    ->where('is_admin', 0)
                    ->where('is_suspended', false) // ★ 追加：凍結されたユーザーを除外
                    ->orderByDesc('products_count')
                    ->take(3)
                    ->get();
   
        return view('top', compact('recommendedProducts', 'rankingProducts', 'hashtags', 'featuredCreators'));
    }
}