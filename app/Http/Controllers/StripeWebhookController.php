<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Stripe\Stripe;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        // シークレットキーが設定されていない場合の保護
        if (!$endpointSecret) {
            Log::error('Stripe Webhook Secret is not set.');
            return response()->json(['error' => 'Webhook secret not configured.'], 400);
        }

        try {
            // 署名を検証してイベントを取得
            $event = Webhook::constructEvent(
                $payload, $sigHeader, $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            // 不正なペイロード
            Log::error('Stripe Webhook Error: Invalid payload.');
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // 不正な署名
            Log::error('Stripe Webhook Error: Invalid signature.');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // 決済完了イベント（checkout.session.completed）を処理
        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            // メタデータから order_id を取得
            $orderId = $session->metadata->order_id ?? null;

            if ($orderId) {
                $order = Order::find($orderId);

                if ($order && $order->status === 'pending') {
                    // ステータスを 'ordered'（手配中）などに更新
                    $order->update([
                        'status' => 'ordered',
                        // ついでに確実を期すため stripe_id もここで更新しておく
                        'stripe_id' => $session->id,
                    ]);

                    Log::info("Order #{$orderId} status updated to 'ordered' via Webhook.");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }
}