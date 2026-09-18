<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Message;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    // 個別商品の購入
    public function checkout(Product $product)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // 1. 決済セッション作成前に、先に注文データを 'pending' で作成しておく
        $order = Order::create([
            'user_id'           => Auth::id(),
            'total_amount'      => $product->price,
            'stripe_id'         => null,
            'status'            => 'pending',
            'shipping_address'  => null,
        ]);

        // 2. 注文明細（OrderItem）を作成
        OrderItem::create([
            'order_id'          => $order->id,
            'product_id'        => $product->id,
            'quantity'          => 1,
            'price_at_purchase' => $product->price,
        ]);

        // 3. Stripeのチェックアウトセッションを作成
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'shipping_address_collection' => [
                'allowed_countries' => ['JP', 'US'], 
            ],
            'phone_number_collection' => [
                'enabled' => true,
            ],
            'success_url' => route('checkout.success', ['product' => $product->id], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('products.show', $product->id, true),
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        // 4. stripe_id を実際のセッションIDに更新
        $order->update(['stripe_id' => $session->id]);

        return redirect($session->url, 303);
    }

    public function success(Request $request, Product $product)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $sessionId = $request->query('session_id');

        // セッションIDから注文を特定
        $order = Order::where('stripe_id', $sessionId)->first();

        if (!$order) {
            $order = Order::where('user_id', Auth::id())->latest()->first();
        }

        if ($sessionId && $order) {
            // 配送先情報がまだなければ取得して更新
            if (empty($order->shipping_address)) {
                $stripeSession = Session::retrieve($sessionId, [
                    'expand' => ['shipping_details'],
                ]);

                $details = $stripeSession->shipping_details ?? $stripeSession->customer_details ?? null;
                $shippingAddress = null;
                if ($details) {
                    $shippingAddress = is_object($details) 
                        ? json_decode(json_encode($details), true) 
                        : (array)$details;
                }

                $order->update(['shipping_address' => $shippingAddress]);

                // 出品者へ購入通知（メッセージ）を送信
                if (Auth::id() !== $product->user_id) {
                    Message::send(
                        userId: $product->user_id,
                        type: 'purchase',
                        title: '商品が購入されました',
                        body: Auth::user()->name . ' さんがあなたの商品を購入しました。',
                        userImage: Auth::user()->icon_image,
                        url: null,
                        fromUserId: Auth::id()
                    );
                }
            }

            // ★ 条件をなくし、ここ（セッションIDとオーダーが存在する場所）に到達したら強制的に ordered に更新
            $order->update(['status' => 'ordered']);
        }

        // 既存のレビューを取得
        $existingReview = Review::where('user_id', Auth::id())
                                ->where('product_id', $product->id)
                                ->first();
    
        return view('checkout.success', compact('product', 'existingReview', 'order'));
    }

    // カートからのまとめ買いチェックアウト処理
    public function cartCheckout(Request $request)
    {
        $user = Auth::user();
        $carts = $user->carts()->with('product')->get();

        if ($carts->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'カートに商品がありません。');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = [];
        $totalAmount = 0;

        foreach ($carts as $cart) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $cart->product->name,
                    ],
                    'unit_amount' => $cart->product->price,
                ],
                'quantity' => $cart->quantity,
            ];
            $totalAmount += $cart->product->price * $cart->quantity;
        }

        $order = Order::create([
            'user_id'           => $user->id,
            'total_amount'      => $totalAmount,
            'stripe_id'         => null,
            'status'            => 'pending',
            'shipping_address'  => null,
        ]);

        foreach ($carts as $cart) {
            OrderItem::create([
                'order_id'          => $order->id,
                'product_id'        => $cart->product_id,
                'quantity'          => $cart->quantity,
                'price_at_purchase' => $cart->product->price,
            ]);
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'shipping_address_collection' => [
                'allowed_countries' => ['JP', 'US'],
            ],
            'phone_number_collection' => [
                'enabled' => true,
            ],
            'success_url' => route('cart.checkout.success', [], true) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('cart.index', [], true),
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        $order->update(['stripe_id' => $session->id]);

        return redirect($session->url, 303);
    }

    // カートまとめ買い成功時の処理
    public function cartSuccess(Request $request)
    {
        $user = Auth::user();
        $sessionId = $request->query('session_id');

        $order = Order::where('stripe_id', $sessionId)->with('orderItems.product')->first();

        if (!$order) {
            $order = Order::where('user_id', $user->id)->with('orderItems.product')->latest()->first();
        }

        if (!$order) {
            return redirect()->route('cart.index')->with('error', '注文情報が見つかりませんでした。');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        if ($sessionId) {
            if (empty($order->shipping_address)) {
                $stripeSession = Session::retrieve($sessionId, [
                    'expand' => ['shipping_details'],
                ]);

                $details = $stripeSession->shipping_details ?? $stripeSession->customer_details ?? null;
                $shippingAddress = null;
                if ($details) {
                    $shippingAddress = is_object($details) 
                        ? json_decode(json_encode($details), true) 
                        : (array)$details;
                }

                $order->update(['shipping_address' => $shippingAddress]);

                // 各出品者へ購入通知を送信 ＆ カートを空にする
                foreach ($order->orderItems as $item) {
                    if ($user->id !== $item->product->user_id) {
                        Message::send(
                            userId: $item->product->user_id,
                            type: 'purchase',
                            title: '商品が購入されました',
                            body: $user->name . ' さんがあなたの商品（' . $item->product->name . '）を購入しました。',
                            userImage: $user->icon_image,
                            url: null,
                            fromUserId: $user->id
                        );
                    }
                }

                $user->carts()->delete();
            }

            // ★ カート購入でもここで確実に ordered に更新する
            if ($order->status === 'pending') {
                $order->update(['status' => 'ordered']);
            }
        }

        return view('checkout.cart-success', compact('order'));
    }

    // 注文履歴一覧を表示
    public function history()
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
                       ->with('orderItems.product') 
                       ->latest()
                       ->paginate(10);

        return view('orders.history', compact('orders'));
    }
}