<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ShopController
{
    public function index(Request $request)
    {
        $query = Shop::query()->where('is_active', true);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('verified_only') && $request->verified_only) {
            $query->where('is_verified', true);
        }

        $shops = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'message' => 'Shops retrieved successfully',
            'data' => $shops,
        ]);
    }

    public function show($id)
    {
        $shop = Shop::with('products')->find($id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Shop retrieved successfully',
            'data' => $shop,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'seller') {
            return response()->json([
                'message' => 'Only sellers can create shops',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($user->shop) {
            return response()->json([
                'message' => 'You already have a shop',
            ], Response::HTTP_CONFLICT);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'banner_url' => 'nullable|url',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $validated['user_id'] = $user->id;
        $validated['slug'] = str()->slug($validated['name'] . '-' . uniqid());

        $shop = Shop::create($validated);

        return response()->json([
            'message' => 'Shop created successfully',
            'data' => $shop,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::find($id);

        if (!$shop) {
            return response()->json([
                'message' => 'Shop not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($shop->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|url',
            'banner_url' => 'nullable|url',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $shop->update($validated);

        return response()->json([
            'message' => 'Shop updated successfully',
            'data' => $shop,
        ]);
    }

    public function myShop()
    {
        $user = auth()->user();
        $shop = $user->shop;

        if (!$shop) {
            return response()->json([
                'message' => 'You do not have a shop',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Shop retrieved successfully',
            'data' => $shop,
        ]);
    }
}
