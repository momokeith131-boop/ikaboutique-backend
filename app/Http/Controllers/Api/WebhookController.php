<?php

namespace App\Http\Controllers\Api;

use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WebhookController
{
    public function index()
    {
        $webhooks = Webhook::where('user_id', Auth::id())->get();
        return response()->json($webhooks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'secret' => 'nullable|string|max:255',
            'events' => 'required|array',
            'events.*' => 'string|in:order.created,order.updated,payment.completed,payment.failed,subscription.created,subscription.cancelled',
            'retry_count' => 'sometimes|integer|min:1|max:10',
        ]);

        $webhook = Webhook::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'url' => $validated['url'],
            'secret' => $validated['secret'] ?? null,
            'events' => $validated['events'],
            'is_active' => true,
            'retry_count' => $validated['retry_count'] ?? 3,
        ]);

        return response()->json([
            'message' => 'Webhook créé avec succès',
            'webhook' => $webhook,
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $webhook = Webhook::where('user_id', Auth::id())->find($id);

        if (!$webhook) {
            return response()->json(['message' => 'Webhook non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($webhook);
    }

    public function update(Request $request, $id)
    {
        $webhook = Webhook::where('user_id', Auth::id())->find($id);

        if (!$webhook) {
            return response()->json(['message' => 'Webhook non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'url' => 'sometimes|url|max:255',
            'secret' => 'nullable|string|max:255',
            'events' => 'sometimes|array',
            'events.*' => 'string|in:order.created,order.updated,payment.completed,payment.failed,subscription.created,subscription.cancelled',
            'is_active' => 'sometimes|boolean',
            'retry_count' => 'sometimes|integer|min:1|max:10',
        ]);

        $webhook->update($validated);

        return response()->json([
            'message' => 'Webhook mis à jour avec succès',
            'webhook' => $webhook,
        ]);
    }

    public function destroy($id)
    {
        $webhook = Webhook::where('user_id', Auth::id())->find($id);

        if (!$webhook) {
            return response()->json(['message' => 'Webhook non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $webhook->delete();

        return response()->json(['message' => 'Webhook supprimé avec succès']);
    }

    public function trigger($id)
    {
        $webhook = Webhook::where('user_id', Auth::id())->find($id);

        if (!$webhook) {
            return response()->json(['message' => 'Webhook non trouvé'], Response::HTTP_NOT_FOUND);
        }

        try {
            $response = Http::post($webhook->url, [
                'event' => 'test',
                'data' => [
                    'message' => 'Ceci est un test de webhook',
                    'timestamp' => now()->toISOString(),
                ],
            ]);

            return response()->json([
                'message' => 'Webhook déclenché avec succès',
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors du déclenchement du webhook',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
