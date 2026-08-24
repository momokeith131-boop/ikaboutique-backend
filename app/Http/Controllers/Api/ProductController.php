<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProductController
{
    public function index(Request $request)
    {
        $query = Product::query()->with(['shop', 'category', 'images']);

        // Filters
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('shop_id')) {
            $query->where('shop_id', $request->shop_id);
        }

        if ($request->has('search')) {
            $query->whereRaw("MATCH(name, description) AGAINST(? IN BOOLEAN MODE)", [$request->search]);
        }

        if ($request->has('featured') && $request->featured) {
            $query->where('is_featured', true);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 20);
        $products = $query->paginate($perPage);

        return response()->json([
            'message' => 'Products retrieved successfully',
            'data' => $products,
        ]);
    }

    public function show($id)
    {
        $product = Product::with(['shop', 'category', 'images', 'variations', 'reviews'])->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Increment views
        $product->increment('views_count');

        return response()->json([
            'message' => 'Product retrieved successfully',
            'data' => $product,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'seller') {
            return response()->json([
                'message' => 'Only sellers can create products',
            ], Response::HTTP_FORBIDDEN);
        }

        $shop = $user->shop;
        if (!$shop) {
            return response()->json([
                'message' => 'Please create a shop first',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'sku' => 'nullable|unique:products',
            'barcode' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'image_url' => 'nullable|url',
            'is_featured' => 'boolean',
        ]);

        $validated['shop_id'] = $shop->id;
        $validated['published_at'] = now();

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($product->shop_id !== $user->shop?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'description' => 'string',
            'price' => 'numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully',
            'data' => $product,
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Product not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($product->shop_id !== $user->shop?->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
