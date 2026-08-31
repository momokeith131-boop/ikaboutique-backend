<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReviewController
{
    // Lister les avis d'un produit
    public function index($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $reviews = Review::with('user')
            ->where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculer la moyenne des notes
        $averageRating = $reviews->avg('rating') ?: 0;
        $totalReviews = $reviews->count();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'summary' => [
                'average_rating' => round($averageRating, 1),
                'total_reviews' => $totalReviews,
            ],
            'reviews' => $reviews,
        ]);
    }

    // Ajouter un avis
    public function store(Request $request, $productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Produit non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string|url',
        ]);

        // Vérifier si l'utilisateur a déjà donné un avis pour ce produit
        $existingReview = Review::where('product_id', $productId)
            ->where('user_id', Auth::id())
            ->first();

        if ($existingReview) {
            return response()->json([
                'message' => 'Vous avez déjà donné un avis pour ce produit',
                'review' => $existingReview,
            ], Response::HTTP_BAD_REQUEST);
        }

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => Auth::id(),
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'images' => $validated['images'] ?? null,
            'is_verified' => $this->checkIfVerified($productId),
        ]);

        return response()->json([
            'message' => 'Avis ajouté avec succès',
            'review' => $review->load('user'),
        ], Response::HTTP_CREATED);
    }

    // Voir un avis
    public function show($id)
    {
        $review = Review::with(['user', 'product'])->find($id);

        if (!$review) {
            return response()->json(['message' => 'Avis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($review);
    }

    // Modifier un avis
    public function update(Request $request, $id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Avis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string|url',
        ]);

        $review->update($validated);

        return response()->json([
            'message' => 'Avis mis à jour avec succès',
            'review' => $review->load('user'),
        ]);
    }

    // Supprimer un avis
    public function destroy($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Avis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($review->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $review->delete();

        return response()->json(['message' => 'Avis supprimé avec succès']);
    }

    // Aimer un avis
    public function like($id)
    {
        $review = Review::find($id);

        if (!$review) {
            return response()->json(['message' => 'Avis non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $review->increment('likes');

        return response()->json([
            'message' => 'Avis aimé avec succès',
            'likes' => $review->likes,
        ]);
    }

    // Vérifier si l'utilisateur a acheté le produit
    private function checkIfVerified($productId)
    {
        // Vérifier si l'utilisateur a une commande avec ce produit
        $hasOrder = \App\Models\Order::where('user_id', Auth::id())
            ->whereHas('items', function ($query) use ($productId) {
                $query->where('product_id', $productId);
            })
            ->exists();

        return $hasOrder;
    }
}
