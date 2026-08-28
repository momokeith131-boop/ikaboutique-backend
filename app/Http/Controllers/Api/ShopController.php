<?php

namespace App\Http\Controllers\Api;

use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ShopController
{
    public function index()
    {
        $shops = Shop::with('user')->get();
        return response()->json($shops);
    }

    public function show($id)
    {
        $shop = Shop::with('user')->find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($shop);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:shops',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        $shop = Shop::create($validated);
        return response()->json($shop, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:shops,slug,' . $id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string',
        ]);

        $shop->update($validated);
        return response()->json($shop);
    }

    public function destroy($id)
    {
        $shop = Shop::find($id);
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }

        $shop->delete();
        return response()->json(['message' => 'Shop deleted successfully']);
    }

    public function myShop()
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        if (!$shop) {
            return response()->json(['message' => 'Shop not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($shop);
    }
}

