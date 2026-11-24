<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Stripe\Webhook;
use App\Models\Order;

class StripeWebhookController
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            env('STRIPE_WEBHOOK_SECRET')
        );

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;

            $cartId = $paymentIntent->metadata->cart_id;

            $order = Order::where('cart_id', $cartId)->first();

            if ($order) {
                $order->payment_status = 'paid';
                $order->status = 'processing';
                $order->save();
            }
        }

        return response()->json(['status' => 'ok']);
    }
}