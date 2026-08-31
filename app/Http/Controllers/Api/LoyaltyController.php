<?php

namespace App\Http\Controllers\Api;

use App\Models\LoyaltySetting;
use App\Models\LoyaltyPoint;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoyaltyController
{
    // Voir les paramètres de fidélité
    public function settings()
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $settings = LoyaltySetting::where('shop_id', $shop->id)->first();

        if (!$settings) {
            // Créer des paramètres par défaut
            $settings = LoyaltySetting::create([
                'shop_id' => $shop->id,
                'points_per_1000' => 10,
                'points_to_cash_rate' => 1.00,
                'is_active' => true,
            ]);
        }

        return response()->json($settings);
    }

    // Mettre à jour les paramètres de fidélité
    public function updateSettings(Request $request)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'points_per_1000' => 'sometimes|integer|min:1',
            'points_to_cash_rate' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $settings = LoyaltySetting::where('shop_id', $shop->id)->first();

        if (!$settings) {
            $settings = LoyaltySetting::create([
                'shop_id' => $shop->id,
                'points_per_1000' => 10,
                'points_to_cash_rate' => 1.00,
                'is_active' => true,
            ]);
        }

        $settings->update($validated);

        return response()->json([
            'message' => 'Paramètres mis à jour avec succès',
            'settings' => $settings,
        ]);
    }

    // Voir les points d'un client
    public function customerPoints($customerId)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $customer = User::find($customerId);

        if (!$customer || $customer->role !== 'customer') {
            return response()->json(['message' => 'Client non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $totalPoints = LoyaltyPoint::getTotalPoints($customerId, $shop->id);

        $settings = LoyaltySetting::where('shop_id', $shop->id)->first();

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ],
            'total_points' => $totalPoints,
            'points_value' => $settings ? $totalPoints * $settings->points_to_cash_rate : 0,
        ]);
    }

    // Lister les points d'un client
    public function customerPointsHistory($customerId)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $points = LoyaltyPoint::where('user_id', $customerId)
            ->where('shop_id', $shop->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($points);
    }
}
