<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController
{
    // Lister les commandes de l'utilisateur connecté
    public function index()
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    // Détail d'une commande
    public function show($id)
    {
        $order = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($order);
    }

    // Créer une commande à partir du panier
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'payment_method' => 'required|string|in:card,paypal,mobile_money',
        ]);

        $user = Auth::user();

        // Récupérer le panier de l'utilisateur
        $cart = Cart::with('items.product')->where('user_id', $user->id)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], Response::HTTP_BAD_REQUEST);
        }

        // Calculer le total
        $total = $cart->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Créer la commande
        $order = Order::create([
            'user_id' => $user->id,
            'shop_id' => $cart->items->first()->product->shop_id,
            'total' => $total,
            'status' => 'pending',
            'shipping_address' => $validated['shipping_address'],
            'billing_address' => $validated['billing_address'] ?? $validated['shipping_address'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => 'pending',
        ]);

        // Créer les articles de la commande à partir du panier
        foreach ($cart->items as $cartItem) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $cartItem->product_id,
                'quantity' => $cartItem->quantity,
                'price' => $cartItem->price,
            ]);
        }

        // Vider le panier après la commande
        $cart->items()->delete();

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order->load('items.product'),
        ], Response::HTTP_CREATED);
    }

    // Mettre à jour le statut d'une commande
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est le propriétaire de la commande ou un admin
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $order->status = $validated['status'];
        $order->save();

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order,
        ]);
    }
}
