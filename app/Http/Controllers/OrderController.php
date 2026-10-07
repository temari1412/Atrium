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
        Stripe::setApiKey(config('services.stripe.secret'));//stripe.secretキー呼び出し

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
        $order = Order::where('stripe_id', $sessionId)
            ->where('user_id', Auth::id())
            ->first();
    
        // 注文が見つからなかった場合
        if (!$order) {
            return redirect()
                ->route('products.show', $product->id)
                ->with('error', '注文情報が見つかりませんでした。');
        }
    
        // 決済完了の検証
        if ($sessionId) {
            // Stripeから最新のセッション情報を取得して、支払いが完了しているか確認する
            // $sessionId が存在する場合のみStripeへ問い合わせる
            $stripeSession = Session::retrieve($sessionId, [
                'expand' => ['shipping_details'], // 問い合わせる際、配送先の住所詳細データも含めて一緒に取得
            ]);
    
            // 支払いが完了（paid）している場合のみ注文を確定する
            if ($stripeSession->payment_status === 'paid') {
    
                // すでに注文確定済みの場合は、再度購入処理を行わない
                if ($order->status !== 'ordered') {
    
                    // 配送先情報がまだなければ取得して更新
                    if (empty($order->shipping_address)) {
                        $details = $stripeSession->shipping_details ?? $stripeSession->customer_details ?? null; // ??（null合体演算子）：左側の値が null だった場合に、右側の値を採用する
                        $shippingAddress = null;
    
                        if ($details) {
                            $shippingAddress = is_object($details)
                                ? json_decode(json_encode($details), true) // ? :（三項演算子）：「もし条件が真ならA、偽ならB」
                                : (array)$details;
                        }
    
                        $order->update(['shipping_address' => $shippingAddress]);
    
                        // 出品者へ購入通知（メッセージ）を送信
                        if (Auth::id() !== $product->user_id) {
                            Message::send(
                                userId: $product->user_id,
                                type: 'purchase',
                                title: '商品が購入されました',
                                body: Auth::user()->name . ' さんがあなたの商品（' . $product->name . '）を購入しました。',
                                userImage: Auth::user()->icon_image,
                                url: route('products.show', $product->id),
                                fromUserId: Auth::id()
                            );
                        }
                    }
    
                    // 注文ステータスを正式に更新
                    $order->update(['status' => 'ordered']);
                }
            } else {
                // 支払い未完了の場合はエラー画面やリダイレクトにするなどの処理
                return redirect()
                    ->route('products.show', $product->id)
                    ->with('error', '決済が完了していません。');
            }
        }
    
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

            $order = Order::where('stripe_id', $sessionId)
            ->where('user_id', $user->id)
            ->with('orderItems.product')
            ->first();
        
        if (!$order) {
            return redirect()
                ->route('cart.index')
                ->with('error', '注文情報が見つかりませんでした。');
        }

            Stripe::setApiKey(config('services.stripe.secret'));

    // Stripe側で決済状態を確認
    $stripeSession = Session::retrieve($sessionId, [
        'expand' => ['shipping_details'],
    ]);

    // 支払いが完了していなければ注文を確定しない
    if ($stripeSession->payment_status !== 'paid') {
        return redirect()
            ->route('cart.index')
            ->with('error', '決済が完了していません。');
    }

    // pending の注文だけを確定
    if ($order->status === 'pending') {

        $details = $stripeSession->shipping_details
            ?? $stripeSession->customer_details
            ?? null;

        $shippingAddress = null;

        if ($details) {
            $shippingAddress = is_object($details)
                ? json_decode(json_encode($details), true)
                : (array) $details;
        }

        $order->update([
            'shipping_address' => $shippingAddress,
            'status' => 'ordered',
        ]);

        // 各出品者へ購入通知
        foreach ($order->orderItems as $item) {
            if ($user->id !== $item->product->user_id) {
                Message::send(
                    userId: $item->product->user_id,
                    type: 'purchase',
                    title: '商品が購入されました',
                    body: $user->name . ' さんがあなたの商品（' . $item->product->name . '）を購入しました。',
                    userImage: $user->icon_image,
                    url: route('products.show', $item->product->id),
                    fromUserId: $user->id
                );
            }
        }

        // 決済完了後にカートを空にする
        $user->carts()->delete();
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