<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ChatController
{
    // Lister les conversations d'un commerçant
    public function conversations()
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $conversations = Conversation::with(['customer', 'lastMessage'])
            ->where('shop_id', $shop->id)
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'customer' => $conv->customer,
                    'last_message' => $conv->lastMessage,
                    'unread_count' => $conv->unread_count,
                    'last_message_at' => $conv->last_message_at,
                ];
            });

        return response()->json($conversations);
    }

    // Lister les conversations d'un client
    public function customerConversations()
    {
        $conversations = Conversation::with(['shop', 'lastMessage'])
            ->where('customer_id', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'shop' => $conv->shop,
                    'last_message' => $conv->lastMessage,
                    'unread_count' => $conv->unread_count,
                    'last_message_at' => $conv->last_message_at,
                ];
            });

        return response()->json($conversations);
    }

    // Voir une conversation
    public function show($id)
    {
        $conversation = Conversation::with(['messages.sender', 'customer', 'shop'])
            ->find($id);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est autorisé
        if ($conversation->customer_id !== Auth::id() && $conversation->shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Marquer les messages comme lus
        if ($conversation->customer_id === Auth::id() || $conversation->shop->user_id === Auth::id()) {
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', Auth::id())
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json($conversation);
    }

    // Créer ou récupérer une conversation
    public function create(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'order_id' => 'nullable|exists:orders,id',
            'message' => 'required|string|max:2000',
        ]);

        // Vérifier si une conversation existe déjà
        $conversation = Conversation::where('shop_id', $validated['shop_id'])
            ->where('customer_id', Auth::id())
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'shop_id' => $validated['shop_id'],
                'customer_id' => Auth::id(),
                'order_id' => $validated['order_id'] ?? null,
                'status' => 'active',
                'last_message_at' => now(),
            ]);
        }

        // Créer le message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => 'text',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'conversation' => $conversation,
            'message' => $message,
        ], Response::HTTP_CREATED);
    }

    // Envoyer un message
    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est autorisé
        if ($conversation->customer_id !== Auth::id() && $conversation->shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'type' => 'sometimes|in:text,image,file',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'text',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'message_data' => $message,
        ]);
    }

    // Marquer les messages comme lus
    public function markAsRead($conversationId)
    {
        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est autorisé
        if ($conversation->customer_id !== Auth::id() && $conversation->shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Messages marqués comme lus']);
    }

    // Compter les messages non lus
    public function unreadCount()
    {
        $user = Auth::user();

        if ($user->role === 'seller') {
            $shop = Shop::where('user_id', $user->id)->first();
            if (!$shop) {
                return response()->json(['unread' => 0]);
            }
            $conversations = Conversation::where('shop_id', $shop->id)->pluck('id');
        } else {
            $conversations = Conversation::where('customer_id', $user->id)->pluck('id');
        }

        $unread = Message::whereIn('conversation_id', $conversations)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread' => $unread]);
    }
}
EOFcat > /workspaces/ikaboutique-backend/app/Http/Controllers/Api/ChatController.php << 'EOF'
<?php

namespace App\Http\Controllers\Api;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ChatController
{
    // Lister les conversations d'un commerçant
    public function conversations()
    {
        $shop = Shop::where('user_id', Auth::id())->first();

        if (!$shop) {
            return response()->json(['message' => 'Aucune boutique trouvée'], Response::HTTP_NOT_FOUND);
        }

        $conversations = Conversation::with(['customer', 'lastMessage'])
            ->where('shop_id', $shop->id)
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'customer' => $conv->customer,
                    'last_message' => $conv->lastMessage,
                    'unread_count' => $conv->unread_count,
                    'last_message_at' => $conv->last_message_at,
                ];
            });

        return response()->json($conversations);
    }

    // Lister les conversations d'un client
    public function customerConversations()
    {
        $conversations = Conversation::with(['shop', 'lastMessage'])
            ->where('customer_id', Auth::id())
            ->orderBy('last_message_at', 'desc')
            ->get()
            ->map(function ($conv) {
                return [
                    'id' => $conv->id,
                    'shop' => $conv->shop,
                    'last_message' => $conv->lastMessage,
                    'unread_count' => $conv->unread_count,
                    'last_message_at' => $conv->last_message_at,
                ];
            });

        return response()->json($conversations);
    }

    // Voir une conversation
    public function show($id)
    {
        $conversation = Conversation::with(['messages.sender', 'customer', 'shop'])
            ->find($id);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est autorisé
        if ($conversation->customer_id !== Auth::id() && $conversation->shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        // Marquer les messages comme lus
        if ($conversation->customer_id === Auth::id() || $conversation->shop->user_id === Auth::id()) {
            Message::where('conversation_id', $conversation->id)
                ->where('sender_id', '!=', Auth::id())
                ->update(['is_read' => true, 'read_at' => now()]);
        }

        return response()->json($conversation);
    }

    // Créer ou récupérer une conversation
    public function create(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'order_id' => 'nullable|exists:orders,id',
            'message' => 'required|string|max:2000',
        ]);

        // Vérifier si une conversation existe déjà
        $conversation = Conversation::where('shop_id', $validated['shop_id'])
            ->where('customer_id', Auth::id())
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'shop_id' => $validated['shop_id'],
                'customer_id' => Auth::id(),
                'order_id' => $validated['order_id'] ?? null,
                'status' => 'active',
                'last_message_at' => now(),
            ]);
        }

        // Créer le message
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => 'text',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'conversation' => $conversation,
            'message' => $message,
        ], Response::HTTP_CREATED);
    }

    // Envoyer un message
    public function sendMessage(Request $request, $conversationId)
    {
        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est autorisé
        if ($conversation->customer_id !== Auth::id() && $conversation->shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'type' => 'sometimes|in:text,image,file',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => Auth::id(),
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'text',
        ]);

        $conversation->last_message_at = now();
        $conversation->save();

        return response()->json([
            'message' => 'Message envoyé avec succès',
            'message_data' => $message,
        ]);
    }

    // Marquer les messages comme lus
    public function markAsRead($conversationId)
    {
        $conversation = Conversation::find($conversationId);

        if (!$conversation) {
            return response()->json(['message' => 'Conversation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier que l'utilisateur est autorisé
        if ($conversation->customer_id !== Auth::id() && $conversation->shop->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', Auth::id())
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Messages marqués comme lus']);
    }

    // Compter les messages non lus
    public function unreadCount()
    {
        $user = Auth::user();

        if ($user->role === 'seller') {
            $shop = Shop::where('user_id', $user->id)->first();
            if (!$shop) {
                return response()->json(['unread' => 0]);
            }
            $conversations = Conversation::where('shop_id', $shop->id)->pluck('id');
        } else {
            $conversations = Conversation::where('customer_id', $user->id)->pluck('id');
        }

        $unread = Message::whereIn('conversation_id', $conversations)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread' => $unread]);
    }
}
