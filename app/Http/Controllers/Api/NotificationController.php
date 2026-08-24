<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use App\Models\NotificationSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class NotificationController
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Notification::where('user_id', $user->id);

        if ($request->has('unread_only') && $request->unread_only) {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));

        return response()->json([
            'message' => 'Notifications retrieved successfully',
            'data' => $notifications,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::find($id);

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $user = auth()->user();
        if ($notification->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notification marked as read',
            'data' => $notification,
        ]);
    }

    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string|unique:notification_subscriptions',
            'device_type' => 'required|in:ios,android,web',
        ]);

        $user = auth()->user();

        $subscription = NotificationSubscription::create([
            'user_id' => $user->id,
            'device_token' => $validated['device_token'],
            'device_type' => $validated['device_type'],
        ]);

        return response()->json([
            'message' => 'Device subscribed to notifications',
            'data' => $subscription,
        ], Response::HTTP_CREATED);
    }

    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string',
        ]);

        $user = auth()->user();
        $subscription = NotificationSubscription::where('user_id', $user->id)
            ->where('device_token', $validated['device_token'])
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found',
            ], Response::HTTP_NOT_FOUND);
        }

        $subscription->delete();

        return response()->json([
            'message' => 'Device unsubscribed from notifications',
        ]);
    }
}
