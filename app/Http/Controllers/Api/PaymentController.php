<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController
{
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string|in:card,paypal,mobile_money',
            'payment_details' => 'nullable|array',
        ]);

        $order = Order::where('user_id', Auth::id())->find($validated['order_id']);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        if ($order->payment_status === 'completed') {
            return response()->json(['message' => 'Order already paid'], Response::HTTP_BAD_REQUEST);
        }

        $transactionId = 'TXN-' . strtoupper(Str::random(12));

        $payment = Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $transactionId,
            'amount' => $order->total,
            'currency' => 'XOF',
            'status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'payment_details' => $validated['payment_details'] ?? null,
        ]);

        $order->payment_status = 'pending';
        $order->save();

        return response()->json([
            'message' => 'Payment initiated successfully',
            'payment' => $payment,
            'redirect_url' => null,
        ], Response::HTTP_CREATED);
    }

    public function confirm($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        if ($payment->order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        if ($payment->status !== 'pending') {
            return response()->json(['message' => 'Payment already processed'], Response::HTTP_BAD_REQUEST);
        }

        $payment->status = 'completed';
        $payment->save();

        $order = $payment->order;
        $order->payment_status = 'completed';
        $order->status = 'processing';
        $order->save();

        return response()->json([
            'message' => 'Payment confirmed successfully',
            'payment' => $payment,
        ]);
    }

    public function getStatus($id)
    {
        $payment = Payment::find($id);
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        if ($payment->order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'payment' => $payment,
        ]);
    }
}
