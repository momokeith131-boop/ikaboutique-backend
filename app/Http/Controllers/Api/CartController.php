<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CartController
{
    // Voir le panier de l'utilisateur connecté
    public function index()
    {
        $cart = Cart::with(['items.product', 'items.variation'])
            ->where('user_id', Auth::id())
            ->first();

        if (!$cart) {
            return response()->json([
                'items' => [],
                'total' => 0,
            ]);
        }

        return response()->json([
            'items' => $cart->items,
            'total' => $cart->total,
        ]);
    }

    // Ajouter un article au panier
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variation_id' => 'nullable|exists:product_variations,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // Récupérer ou créer le panier de l'utilisateur
        $cart = Cart::firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        // Vérifier si l'article existe déjà dans le panier
        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $validated['product_id'])
            ->where('product_variation_id', $validated['product_variation_id'] ?? null)
            ->first();

        if ($existingItem) {
            // Mettre à jour la quantité
            $existingItem->quantity += $validated['quantity'];
            $existingItem->save();
            return response()->json($existingItem);
        }

        // Récupérer le prix du produit
        $product = Product::find($validated['product_id']);
        $price = $product->price;

        // Créer un nouvel article (sans product_variation_id si non fourni)
        $itemData = [
            'cart_id' => $cart->id,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'price' => $price,
        ];

        if (isset($validated['product_variation_id'])) {
            $itemData['product_variation_id'] = $validated['product_variation_id'];
        }

        $item = CartItem::create($itemData);

        return response()->json($item, Response::HTTP_CREATED);
    }

    // Modifier un article du panier
    public function update(Request $request, $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item = CartItem::with('cart')->find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'article appartient bien au panier de l'utilisateur connecté
        if ($item->cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $item->quantity = $validated['quantity'];
        $item->save();

        return response()->json($item);
    }

    // Supprimer un article du panier
    public function remove($itemId)
    {
        $item = CartItem::with('cart')->find($itemId);

        if (!$item) {
            return response()->json(['message' => 'Item not found'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'article appartient bien au panier de l'utilisateur connecté
        if ($item->cart->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $item->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    // Vider le panier
    public function clear()
    {
        $cart = Cart::where('user_id', Auth::id())->first();

        if (!$cart) {
            return response()->json(['message' => 'Cart is already empty']);
        }

        $cart->items()->delete();

        return response()->json(['message' => 'Cart cleared successfully']);
    }
}
