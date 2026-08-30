<?php

namespace App\Http\Controllers\Api;

use App\Models\Accounting;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AccountingController
{
    // Résumé comptable
    public function summary($shopId)
    {
        $shop = Shop::find($shopId);
        if (!$shop) {
            return response()->json(['message' => 'Boutique non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if ($shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $summary = Accounting::getSummary($shopId);

        // Transactions du mois
        $monthlyRevenue = Accounting::where('shop_id', $shopId)
            ->where('type', 'revenue')
            ->where('status', 'completed')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        $monthlyExpenses = Accounting::where('shop_id', $shopId)
            ->where('type', 'expense')
            ->where('status', 'completed')
            ->whereMonth('transaction_date', now()->month)
            ->sum('amount');

        return response()->json([
            'summary' => $summary,
            'monthly' => [
                'revenue' => $monthlyRevenue,
                'expenses' => $monthlyExpenses,
                'profit' => $monthlyRevenue - $monthlyExpenses,
            ],
        ]);
    }

    // Liste des transactions
    public function transactions(Request $request, $shopId)
    {
        $shop = Shop::find($shopId);
        if (!$shop) {
            return response()->json(['message' => 'Boutique non trouvée'], Response::HTTP_NOT_FOUND);
        }

        if ($shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $query = Accounting::where('shop_id', $shopId);

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 20);
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate($perPage);

        return response()->json($transactions);
    }

    // Créer une transaction
    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'type' => 'required|in:revenue,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'reference' => 'nullable|string',
            'transaction_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,completed,cancelled',
        ]);

        $shop = Shop::find($validated['shop_id']);
        if ($shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $transaction = Accounting::create([
            'shop_id' => $validated['shop_id'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'reference' => $validated['reference'] ?? null,
            'transaction_date' => $validated['transaction_date'] ?? now(),
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'message' => 'Transaction créée avec succès',
            'transaction' => $transaction,
        ], Response::HTTP_CREATED);
    }

    // Mettre à jour une transaction
    public function update(Request $request, $id)
    {
        $transaction = Accounting::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $shop = Shop::find($transaction->shop_id);
        if ($shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'type' => 'sometimes|in:revenue,expense',
            'category' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'reference' => 'nullable|string',
            'transaction_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,completed,cancelled',
        ]);

        $transaction->update($validated);

        return response()->json([
            'message' => 'Transaction mise à jour avec succès',
            'transaction' => $transaction,
        ]);
    }

    // Supprimer une transaction
    public function destroy($id)
    {
        $transaction = Accounting::find($id);
        if (!$transaction) {
            return response()->json(['message' => 'Transaction non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $shop = Shop::find($transaction->shop_id);
        if ($shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $transaction->delete();

        return response()->json(['message' => 'Transaction supprimée avec succès']);
    }
}
