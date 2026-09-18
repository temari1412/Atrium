<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Product;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LikeController extends Controller
{
    public function toggle($productId)
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $userId = Auth::id();
        $user = Auth::user();
        $existingLike = Like::where('user_id', $userId)
                            ->where('product_id', $productId)
                            ->first();

        $product = Product::findOrFail($productId);

        if ($existingLike) {
            $existingLike->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            $liked = true;

            // 通知処理で万が一エラーが起きていいね機能を止めないよう try-catch で囲む
            try {
                if (!empty($product->user_id) && $product->user_id !== $userId) {
                    Message::send(
                        userId: $product->user_id,
                        type: 'like',
                        title: '新しい「いいね」が届きました',
                        body: "{$user->name}さんがあなたの作品「{$product->name}」にいいねしました。",
                        userImage: $user->icon_image ?? null,
                        url: route('products.show', $product->id),
                        fromUserId: $userId
                    );
                }
            } catch (\Exception $e) {
                Log::error('Like notification error: ' . $e->getMessage());
            }
        }

        $product->refresh();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $product->likes()->count(),
        ]);
    }
}