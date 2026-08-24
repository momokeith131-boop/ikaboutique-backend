<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CartController
{
    public function index()
    {
        $user = auth()->user();
        $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);

        return response()->json([
            'message' => 'Cart retrieved successfully',
            'data' => $cart->load('items.product'),
            'total' => $cart->items->sum(fn($item) => $item->price * $item->quantity),
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();
        $cart = $user->cart ?? Cart::create(['user_id' => $user->id]);

        $product = Product::find($validated['product_id']);

        $cartItem = CartItem::firstOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $validated['product_id'],
                'product_variation_id' => $validated['product_variation_id'],
            ],
            [
                'price' => $product->price,
                'quantity' => 0,
            ]
        );

        $cartItem->increment('quantity', $validated['quantity']);

        return response()->json([
            'message' => 'Item added to cart',
            'data' => $cartItem,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cartItem = CartItem::find($itemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'message' => 'Cart item updated',
            'data' => $cartItem,
        ]);
    }

    public function remove($itemId)
    {
        $cartItem = CartItem::find($itemId);

        if (!$cartItem) {
            return response()->json([
                'message' => 'Cart item not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart',
        ]);
    }

    public function clear()
    {
        $user = auth()->user();
        $cart = $user->cart;

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'message' => 'Cart cleared',
        ]);
    }
}
