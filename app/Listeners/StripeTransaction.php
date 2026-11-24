<?php

namespace App\Listeners;

use Webkul\Sales\Repositories\OrderTransactionRepository;

class StripeTransaction
{
    public function __construct(
        protected OrderTransactionRepository $orderTransactionRepository
    ) {}

    /**
     * Save Stripe transaction after invoice creation.
     *
     * @param \Webkul\Sales\Models\Invoice $invoice
     * @return void
     */
    public function handle($invoice)
    {
        // Only for Stripe orders
        if ($invoice->order->payment->method !== 'stripe') {
            return;
        }

        $paymentIntent = request()->input('stripe_payment_intent');

        if (! $paymentIntent) {
            return;
        }

        $this->orderTransactionRepository->create([
            'transaction_id' => $paymentIntent['id'],
            'status'         => $paymentIntent['status'],
            'type'           => 'payment_intent',
            'amount'         => $paymentIntent['amount_received'] / 100,
            'payment_method' => 'stripe',
            'order_id'       => $invoice->order->id,
            'invoice_id'     => $invoice->id,
            'data'           => json_encode($paymentIntent),
        ]);
    }
}