<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
    
        // ログインユーザーが出品した商品のID一覧を取得
        $productIds = Product::where('user_id', $user->id)->pluck('id');

        // 閲覧数・いいね数
        $totalViews = Product::where('user_id', $user->id)->sum('views');
        $totalLikes = \App\Models\Like::whereIn('product_id', $productIds)->count();
        
        // 購入数（ログインユーザーの商品が購入された総数量）
        $totalSalesCount = DB::table('order_items')
            ->whereIn('product_id', $productIds)
            ->sum('quantity');

        // 先週比の計算（今週の購入数 先週の購入数）
        $thisWeekSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.created_at', '>=', now()->subWeek())
            ->sum('order_items.quantity');

        $lastWeekSales = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('order_items.product_id', $productIds)
            ->where('orders.created_at', '>=', now()->subWeeks(2))
            ->where('orders.created_at', '<', now()->subWeek())
            ->sum('order_items.quantity');

        $salesDiff = $thisWeekSales - $lastWeekSales;

        //　商品別販売ランキングの取得
        $productRanking = Product::where('user_id', $user->id)
        ->withCount('likes')
        ->withSum('orderItems as total_sold', 'quantity')
        ->orderByDesc('total_sold')
        ->take(5)
        ->get();

        // 年代別売上の取得
        $stats = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereIn('order_items.product_id', $productIds)
            ->selectRaw("
                strftime('%Y-%m', orders.created_at) as month, 
                CASE 
                    WHEN users.birth_date IS NULL THEN '不明'
                    ELSE (CAST((strftime('%Y', 'now') - strftime('%Y', users.birth_date)) / 10 AS INT) * 10)
                end as generation, 
                SUM(order_items.price_at_purchase * order_items.quantity) as total_sales")
            ->groupBy('month', 'generation')
            ->orderBy('month', 'ASC')
            ->get();

        $months = $stats->pluck('month')->unique()->values();
        $generations = $stats->pluck('generation')->unique();
    
        $datasets = [];
        foreach ($generations as $gen) {
            $data = $months->map(function ($month) use ($stats, $gen) {
                return $stats->where('month', $month)->where('generation', $gen)->sum('total_sales');
            });
            
            $label = ($gen === '不明') ? '不明' : $gen . '代';

            $datasets[] = [
                'label' => $label,
                'data' => $data,
                'backgroundColor' => $this->getColorForGeneration($gen)
            ];
        }

        //  性別比率の取得（自分の商品が購入されたデータのみ）
        $genderStats = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereIn('order_items.product_id', $productIds)
            ->selectRaw("
                CASE 
                    WHEN users.gender = 'woman' OR users.gender = 'female' THEN '女性'
                    WHEN users.gender = 'man' OR users.gender = 'male' THEN '男性'
                    WHEN users.gender = 'other' THEN 'その他'
                    ELSE '不明'
                END as gender, 
                SUM(order_items.price_at_purchase * order_items.quantity) as total_sales")
            ->groupBy('gender')
            ->orderByRaw("CASE 
                WHEN gender = '女性' THEN 1
                WHEN gender = '男性' THEN 2
                WHEN gender = 'その他' THEN 3
                ELSE 4
            END")
            ->get();
    
        return view('dashboard', compact(
            'months', 
            'datasets', 
            'genderStats', 
            'totalViews', 
            'totalLikes', 
            'totalSalesCount',
            'salesDiff',
            'productRanking'
        )); 
    }
    
    // 年代ごとに色を返す
    private function getColorForGeneration($gen) {
        $colors = [
            10 => '#f472b6', 
            20 => '#fbbf24', 
            30 => '#34d399', 
            40 => '#3b82f6',
            50 => '#a855f7',
            60 => '#ec4899',
            '不明' => '#9ca3af'
        ];
        return $colors[$gen] ?? '#9ca3af';
    }
}