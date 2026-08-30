<?php

namespace App\Http\Controllers\Api;

use App\Models\Domain;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DomainController
{
    public function index(Request $request)
    {
        $shop = Shop::where('user_id', Auth::id())->first();
        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }
        $domains = Domain::where('shop_id', $shop->id)->get();
        return response()->json(['domains' => $domains, 'shop' => ['id' => $shop->id, 'name' => $shop->name]]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['domain' => 'required|string|unique:domains', 'is_primary' => 'boolean']);
        $shop = Shop::where('user_id', Auth::id())->first();
        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }
        $existingDomains = Domain::where('shop_id', $shop->id)->count();
        $isPrimary = $validated['is_primary'] ?? ($existingDomains === 0);
        $domain = Domain::create(['shop_id' => $shop->id, 'domain' => $validated['domain'], 'is_primary' => $isPrimary, 'status' => 'pending']);
        return response()->json(['message' => 'Domaine ajouté avec succès', 'domain' => $domain], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $domain = Domain::with('shop')->find($id);
        if (!$domain) return response()->json(['message' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        if ($domain->shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        return response()->json($domain);
    }

    public function update(Request $request, $id)
    {
        $domain = Domain::find($id);
        if (!$domain) return response()->json(['message' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        if ($domain->shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        $validated = $request->validate(['domain' => 'sometimes|string|unique:domains,domain,' . $id, 'status' => 'sometimes|in:pending,verified,active,failed']);
        $domain->update($validated);
        return response()->json(['message' => 'Domaine mis à jour avec succès', 'domain' => $domain]);
    }

    public function destroy($id)
    {
        $domain = Domain::find($id);
        if (!$domain) return response()->json(['message' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        if ($domain->shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        $domain->delete();
        return response()->json(['message' => 'Domaine supprimé avec succès']);
    }

    public function verify($id)
    {
        $domain = Domain::find($id);
        if (!$domain) return response()->json(['message' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        if ($domain->shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        $domain->status = 'verified';
        $domain->verified_at = now();
        $domain->save();
        return response()->json(['message' => 'Domaine vérifié avec succès', 'domain' => $domain]);
    }

    public function setPrimary($id)
    {
        $domain = Domain::find($id);
        if (!$domain) return response()->json(['message' => 'Domaine non trouvé'], Response::HTTP_NOT_FOUND);
        if ($domain->shop->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }
        if ($domain->status !== 'verified' && $domain->status !== 'active') {
            return response()->json(['message' => 'Ce domaine n\'est pas encore vérifié.'], Response::HTTP_BAD_REQUEST);
        }
        Domain::where('shop_id', $domain->shop_id)->update(['is_primary' => false]);
        $domain->is_primary = true;
        $domain->save();
        return response()->json(['message' => 'Domaine défini comme primaire', 'domain' => $domain]);
    }
}
