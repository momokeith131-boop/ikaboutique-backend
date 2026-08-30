<?php

namespace App\Http\Controllers\Api;

use App\Models\Statistic;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class StatisticController
{
    // Enregistrer un événement
    public function track(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|in:visit,view_product,add_to_cart,checkout,order,payment,click_whatsapp',
            'shop_id' => 'nullable|exists:shops,id',
            'metadata' => 'nullable|array',
        ]);

        $statistic = Statistic::track($validated['event_type'], [
            'shop_id' => $validated['shop_id'] ?? null,
            'metadata' => $validated['metadata'] ?? null,
        ]);

        return response()->json([
            'message' => 'Event tracked successfully',
            'statistic' => $statistic,
        ]);
    }

    // Statistiques d'une boutique
    public function shopStats($shopId)
    {
        // Vérifier que la boutique appartient à l'utilisateur ou que l'utilisateur est admin
        $user = Auth::user();
        $shop = \App\Models\Shop::find($shopId);

        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        if ($shop->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $stats = Statistic::getStats($shopId);

        // Produits populaires
        $popularProducts = \App\Models\Product::where('shop_id', $shopId)
            ->withCount('views')
            ->orderBy('views_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'popular_products' => $popularProducts,
        ]);
    }

    // Statistiques globales (admin seulement)
    public function globalStats()
    {
        $user = Auth::user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $stats = Statistic::getStats();

        // Statistiques par boutique
        $shops = \App\Models\Shop::withCount('orders')->get()->map(function ($shop) {
            $shopStats = Statistic::where('shop_id', $shop->id);
            return [
                'shop_id' => $shop->id,
                'name' => $shop->name,
                'visits' => $shopStats->where('event_type', 'visit')->count(),
                'orders' => $shop->orders_count,
                'revenue' => $shop->orders()->sum('total'),
            ];
        });

        return response()->json([
            'global' => $stats,
            'shops' => $shops,
        ]);
    }
}
