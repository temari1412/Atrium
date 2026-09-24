<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Review, Product, Order};
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // レビュー作成画面（既存レビューがあれば編集モードとして表示）
    public function create($id)
    {
        $product = Product::findOrFail($id);
        $review = Review::where('user_id', Auth::id())
                        ->where('product_id', $product->id)
                        ->first();
                        
        return view('reviews.create', compact('product', 'review'));
    }

    // レビュー編集画面（個別のレビューIDから取得）
    public function edit($id, $reviewId)
    {
        $product = Product::findOrFail($id);
        $review = Review::findOrFail($reviewId);
        return view('reviews.edit', compact('product', 'review'));
    }

    // レビューの保存（新規・更新兼用）
    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5', 
            'comment' => 'required|string|max:500'
        ]);

        Review::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $id],
            [
                'rating' => $request->rating, 
                'comment' => $request->comment, 
                'ai_status' => 'public'
            ]
        );

        return redirect()->route('products.show', $id)
                         ->with('success', 'レビューを投稿・更新しました！');
    }

    // レビューの更新（edit経由の保存用）
    public function update(Request $request, $id, $reviewId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5', 
            'comment' => 'required|string|max:500'
        ]);

        $review = Review::findOrFail($reviewId);
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

        $userId = Auth::id();

        if ($request->has('reviews')) {
            foreach ($request->reviews as $productId => $data) {
                Review::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'product_id' => $productId,
                    ],
                    [
                        'rating' => $data['rating'] ?? 5,
                        'comment' => $data['comment'] ?? '', // ← ここを null から空文字に変更しました
                        'ai_status' => 'public',
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
        $review = Review::findOrFail($id);
        $review->delete();

        return back()->with('success', 'レビューを削除しました');
    }
}