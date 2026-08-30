<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProductController
{
    public function index(Request $request)
    {
        $cacheKey = 'products_' . md5(json_encode($request->all()));
        
        $products = cache()->remember($cacheKey, 600, function () use ($request) {
            $query = Product::with(['shop', 'category', 'images']);

            if ($request->has('search')) {
                $query->where('name', 'LIKE', '%' . $request->search . '%');
            }

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('shop_id')) {
                $query->where('shop_id', $request->shop_id);
            }

            if ($request->has('price_min')) {
                $query->where('price', '>=', $request->price_min);
            }

            if ($request->has('price_max')) {
                $query->where('price', '<=', $request->price_max);
            }

            if ($request->has('sort_by')) {
                $direction = $request->get('sort_direction', 'asc');
                $query->orderBy($request->sort_by, $direction);
            }

            $perPage = $request->get('per_page', 15);
            return $query->paginate($perPage);
        });

        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with(['shop', 'category', 'images'])->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($product);
    }

    public function store(Request $request)
    {
        if (!Gate::allows('create', Product::class)) {
            return response()->json(['message' => 'Forbidden - You are not a seller or admin'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'shop_id' => 'required|exists:shops,id',
            'images' => 'nullable|array',
        ]);

        $product = Product::create($validated);
        cache()->forget('products_');
        return response()->json($product, Response::HTTP_CREATED);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        if (!Gate::allows('update', $product)) {
            return response()->json(['message' => 'Forbidden - You cannot edit this product'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:products,slug,' . $id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'images' => 'nullable|array',
        ]);

        $product->update($validated);
        cache()->forget('products_');
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        if (!Gate::allows('delete', $product)) {
            return response()->json(['message' => 'Forbidden - You cannot delete this product'], Response::HTTP_FORBIDDEN);
        }

        $product->delete();
        cache()->forget('products_');
        return response()->json(['message' => 'Product deleted successfully'], Response::HTTP_OK);
    }

    public function addImage(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'image_url' => 'required|string|max:255',
            'is_primary' => 'boolean',
        ]);

        $image = $product->images()->create($validated);
        return response()->json($image, Response::HTTP_CREATED);
    }

    public function deleteImage($imageId)
    {
        $image = ProductImage::find($imageId);
        if (!$image) {
            return response()->json(['message' => 'Image not found'], Response::HTTP_NOT_FOUND);
        }

        $image->delete();
        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function getImages($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($product->images);
    }

    public function clearCache()
    {
        cache()->flush();
        return response()->json(['message' => 'Cache cleared successfully']);
    }
}

    // Produits en stock faible
    public function lowStock()
    {
        $products = Product::where('stock', '<=', 'low_stock_threshold')
            ->with('shop')
            ->get();

        return response()->json([
            'products' => $products,
            'total' => $products->count(),
        ]);
    }

    // Réapprovisionner un produit
    public function restock(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $oldStock = $product->stock;
        $product->stock += $validated['quantity'];
        $product->save();

        return response()->json([
            'message' => 'Product restocked successfully',
            'product' => $product,
            'old_stock' => $oldStock,
            'added' => $validated['quantity'],
        ]);
    }

    // Historique des stocks
    public function stockHistory($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'product' => $product,
            'history' => [
                ['date' => $product->created_at, 'event' => 'created', 'stock' => $product->stock],
                // Pour l'instant on simule, on pourra ajouter une table d'historique plus tard
            ],
        ]);
    }
