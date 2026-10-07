<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Review, Product, Order, OrderItem};
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // レビュー作成画面
    public function create($id)
    {
        $product = Product::findOrFail($id);
        //ログイン中のユーザーが、この商品を含む注文をしたことがあるか
        $hasPurchased = OrderItem::whereHas('order', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->where('product_id', $product->id)
        ->exists();

        if (!$hasPurchased) {
            return redirect()->route('products.show', $product->id)
                             ->with('error', '購入した商品だけレビューを投稿できます。');
        }

        $review = Review::where('user_id', Auth::id())
                        ->where('product_id', $product->id)
                        ->first();

        if ($review) {
            return redirect()->route('products.show', $product->id)
                             ->with('error', 'この商品にはすでにレビューがあります。');
        }

        return view('reviews.create', compact('product', 'review'));
    }

    // レビュー編集画面
    // public function edit($id, $reviewId)
    // {
    //     $product = Product::findOrFail($id);

    //     $review = Review::where('id', $reviewId)
    //                     ->where('user_id', Auth::id())
    //                     ->where('product_id', $id)
    //                     ->firstOrFail();

    //     return view('reviews.edit', compact('product', 'review'));
    // }

    // レビューの保存
    public function store(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $hasPurchased = OrderItem::whereHas('order', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->where('product_id', $product->id)
        ->exists();

        if (!$hasPurchased) {
            return redirect()->route('products.show', $product->id)
                             ->with('error', '購入した商品だけレビューを投稿できます。');
        }

        $existingReview = Review::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->first();

        if ($existingReview) {
            return redirect()->route('products.show', $product->id)
                             ->with('error', 'この商品にはすでにレビューがあります。');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500'
        ]);

        Review::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            // 'ai_status' => 'public'
        ]);

        return redirect()->route('products.show', $id)
                         ->with('success', 'レビューを投稿しました！');
    }

    // レビューの更新
    public function update(Request $request, $id, $reviewId)
    {
        $review = Review::where('id', $reviewId)
                        ->where('user_id', Auth::id())
                        ->where('product_id', $id)
                        ->firstOrFail();

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:500'
        ]);

        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return redirect()->route('products.show', $id)
                         ->with('success', 'レビューを更新しました！');
    }

    // 注文完了画面からのレビュー一括保存
    public function storeBatch(Request $request, $orderId)
    {
        $request->validate([
            'reviews.*.rating' => 'required|integer|min:1|max:5',
            'reviews.*.comment' => 'nullable|string|max:500',
        ]);

        $order = Order::where('id', $orderId)
                      ->where('user_id', Auth::id())
                      ->firstOrFail();

        $userId = Auth::id();

        if ($request->has('reviews')) {
            foreach ($request->reviews as $productId => $data) {
                $purchased = OrderItem::where('order_id', $order->id)
                                      ->where('product_id', $productId)
                                      ->exists();

                if (!$purchased) {
                    continue;
                }

                Review::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'product_id' => $productId,
                    ],
                    [
                        'rating' => $data['rating'] ?? 5,
                        'comment' => $data['comment'] ?? '',
                        // 'ai_status' => 'public',
                    ]
                );
            }
        }

        return redirect()->route('top')
                         ->with('success', 'レビューをまとめて投稿しました！');
    }

    // 削除処理
    public function destroy($id)
    {
        $review = Review::where('id', $id)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        $review->delete();

        return back()->with('success', 'レビューを削除しました');
    }
}

