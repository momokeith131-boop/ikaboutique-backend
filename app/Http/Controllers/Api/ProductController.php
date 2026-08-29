<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProductController
{
    // Liste des produits (public)
    public function index()
    {
        $products = Product::with(['shop', 'category', 'images'])->get();
        return response()->json($products);
    }

    // Détail d'un produit (public)
    public function show($id)
    {
        $product = Product::with(['shop', 'category', 'images'])->find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
        return response()->json($product);
    }

    // Créer un produit (vendeur uniquement)
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
        return response()->json($product, Response::HTTP_CREATED);
    }

    // Modifier un produit (vendeur uniquement)
    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
        if (!Gate::allows('update', $product)) {
            return response()->json(['message' => 'Forbidden - You cannot edit this product'], Response::HTTP_FORBIDDEN);
        }
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
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
        return response()->json($product);
    }

    // Supprimer un produit (vendeur uniquement)
    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
        if (!Gate::allows('delete', $product)) {
            return response()->json(['message' => 'Forbidden - You cannot delete this product'], Response::HTTP_FORBIDDEN);
        }
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $product->delete();
        return response()->json(['message' => 'Product deleted successfully'], Response::HTTP_OK);
    }

    // Ajouter une image à un produit
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

    // Supprimer une image
    public function deleteImage($imageId)
    {
        $image = ProductImage::find($imageId);
        if (!$image) {
            return response()->json(['message' => 'Image not found'], Response::HTTP_NOT_FOUND);
        }

        $image->delete();
        return response()->json(['message' => 'Image deleted successfully']);
    }

    // Lister les images d'un produit
    public function getImages($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($product->images);
    }
}
