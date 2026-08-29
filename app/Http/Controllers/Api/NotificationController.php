<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Models\NotificationSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NotificationController
{
    // Lister les notifications de l'utilisateur connecté
    public function index()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notifications->where('is_read', false)->count(),
        ]);
    }

    // Marquer une notification comme lue
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        $notification->is_read = true;
        $notification->read_at = now();
        $notification->save();

        return response()->json(['message' => 'Notification marked as read']);
    }

    // S'abonner aux notifications
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:email,sms,push',
            'target' => 'required|string|max:255',
        ]);

        // Vérifier si l'abonnement existe déjà
        $subscription = NotificationSubscription::where('user_id', Auth::id())
            ->where('type', $validated['type'])
            ->where('target', $validated['target'])
            ->first();

        if ($subscription) {
            $subscription->is_active = true;
            $subscription->save();
            return response()->json(['message' => 'Subscription already exists and is now active']);
        }

        $subscription = NotificationSubscription::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'target' => $validated['target'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Subscribed successfully',
            'subscription' => $subscription,
        ], Response::HTTP_CREATED);
    }

    // Se désabonner des notifications
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:email,sms,push',
            'target' => 'required|string|max:255',
        ]);

        $subscription = NotificationSubscription::where('user_id', Auth::id())
            ->where('type', $validated['type'])
            ->where('target', $validated['target'])
            ->first();

        if (!$subscription) {
            return response()->json(['message' => 'Subscription not found'], Response::HTTP_NOT_FOUND);
        }

        $subscription->is_active = false;
        $subscription->save();

        return response()->json(['message' => 'Unsubscribed successfully']);
    }

    // Créer une notification (pour les vendeurs/admin)
    public function createNotification(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'sometimes|string|in:info,success,warning,error',
            'link' => 'nullable|string|max:255',
        ]);

        $notification = Notification::create([
            'user_id' => $validated['user_id'],
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'info',
            'link' => $validated['link'] ?? null,
        ]);

        return response()->json($notification, Response::HTTP_CREATED);
    }
}
