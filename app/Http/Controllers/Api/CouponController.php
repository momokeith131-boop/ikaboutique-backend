<?php

namespace App\Http\Controllers\Api;

use App\Models\Coupon;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CouponController
{
    // Lister les coupons
    public function index()
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $coupons = Coupon::where('shop_id', $shop->id)->get();

        return response()->json($coupons);
    }

    // Créer un coupon
    public function store(Request $request)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'code' => 'required|string|unique:coupons|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
        ]);

        $coupon = Coupon::create([
            'shop_id' => $shop->id,
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'value' => $validated['value'],
            'min_order_amount' => $validated['min_order_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_until' => $validated['valid_until'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Coupon créé avec succès',
            'coupon' => $coupon,
        ], Response::HTTP_CREATED);
    }

    // Voir un coupon
    public function show($id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $coupon = Coupon::where('shop_id', $shop->id)->find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($coupon);
    }

    // Mettre à jour un coupon
    public function update(Request $request, $id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $coupon = Coupon::where('shop_id', $shop->id)->find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $id,
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after:valid_from',
            'is_active' => 'sometimes|boolean',
        ]);

        $coupon->update($validated);

        return response()->json([
            'message' => 'Coupon mis à jour avec succès',
            'coupon' => $coupon,
        ]);
    }

    // Supprimer un coupon
    public function destroy($id)
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $coupon = Coupon::where('shop_id', $shop->id)->find($id);

        if (!$coupon) {
            return response()->json(['message' => 'Coupon non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $coupon->delete();

        return response()->json(['message' => 'Coupon supprimé avec succès']);
    }

    // Valider un coupon
    public function validateCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', strtoupper($validated['code']))->first();

        if (!$coupon) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon invalide',
            ]);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupon expiré ou inactif',
            ]);
        }

        if ($coupon->min_order_amount && $validated['subtotal'] < $coupon->min_order_amount) {
            return response()->json([
                'valid' => false,
                'message' => 'Montant minimum requis : ' . number_format($coupon->min_order_amount, 2) . ' FCFA',
            ]);
        }

        $discount = $coupon->applyDiscount($validated['subtotal']);

        return response()->json([
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'Coupon valide ! Réduction de ' . number_format($discount, 2) . ' FCFA',
        ]);
    }
}
