<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController
{
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,mobile_money,bank_transfer',
        ]);

        $order = Order::find($validated['order_id']);

        $user = auth()->user();
        if ($order->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        // Create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'payment_method' => $validated['payment_method'],
            'gateway' => $this->getGateway($validated['payment_method']),
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);

        // TODO: Integrate with actual payment gateway (Stripe, Orange Money, etc.)
        // For now, return mock response
        return response()->json([
            'message' => 'Payment initiated',
            'data' => $payment,
            'payment_url' => 'https://payment-gateway.example.com/pay/' . $payment->id,
        ]);
    }

    public function confirm(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'transaction_id' => 'required|string',
            'status' => 'required|in:completed,failed',
        ]);

        $payment->update([
            'transaction_id' => $validated['transaction_id'],
            'status' => $validated['status'],
            'processed_at' => now(),
        ]);

        // Update order payment status
        if ($validated['status'] === 'completed') {
            $payment->order->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Payment confirmed',
            'data' => $payment,
        ]);
    }

    public function getStatus($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Payment not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Payment status retrieved',
            'data' => $payment,
        ]);
    }

    private function getGateway($method)
    {
        $gateways = [
            'credit_card' => 'stripe',
            'mobile_money' => 'orange_money',
            'bank_transfer' => 'bank',
        ];

        return $gateways[$method] ?? 'stripe';
    }
}
