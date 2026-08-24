<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class OrderController
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Order::query()->where('user_id', $user->id);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));

        return response()->json([
            'message' => 'Orders retrieved successfully',
            'data' => $orders,
        ]);
    }

    public function show($id)
    {
        $order = Order::with('items.product', 'payments')->find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($order->user_id !== $user->id && $order->shop_id !== $user->shop?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'message' => 'Order retrieved successfully',
            'data' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'shipping_address' => 'required|array',
            'billing_address' => 'nullable|array',
        ]);

        $user = auth()->user();
        $cart = $user->cart;

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'message' => 'Cart is empty',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Group items by shop
        $itemsByShop = $cart->items->groupBy('product.shop_id');

        $orders = [];
        foreach ($itemsByShop as $shopId, $items) {
            $subtotal = $items->sum(fn($item) => $item->price * $item->quantity);
            $taxAmount = $subtotal * 0.18; // 18% tax
            $shippingCost = 5000; // 5000 CFA
            $totalAmount = $subtotal + $taxAmount + $shippingCost;

            $order = Order::create([
                'order_number' => 'ORD-' . Str::upper(Str::random(8)),
                'user_id' => $user->id,
                'shop_id' => $shopId,
                'status' => 'pending',
                'payment_status' => 'pending',
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'discount_amount' => 0,
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'],
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
            ]);

            // Create order items
            foreach ($items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_variation_id' => $cartItem->product_variation_id,
                    'product_name' => $cartItem->product->name,
                    'product_sku' => $cartItem->product->sku,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->price * $cartItem->quantity,
                ]);
            }

            $orders[] = $order;
        }

        // Clear cart
        $cart->items()->delete();

        return response()->json([
            'message' => 'Orders created successfully',
            'data' => $orders,
        ], Response::HTTP_CREATED);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Order not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($order->shop_id !== $user->shop?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order->update(['status' => $validated['status']]);

        if ($validated['status'] === 'shipped') {
            $order->update(['shipped_at' => now()]);
        } elseif ($validated['status'] === 'delivered') {
            $order->update(['delivered_at' => now()]);
        }

        return response()->json([
            'message' => 'Order status updated',
            'data' => $order,
        ]);
    }
}
