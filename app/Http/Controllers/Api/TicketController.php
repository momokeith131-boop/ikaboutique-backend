<?php

namespace App\Http\Controllers\Api;

use App\Models\Ticket;
use App\Models\TicketReply;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TicketController
{
    public function index(Request $request)
    {
        $query = Ticket::with(['user', 'shop']);

        if (Auth::user()->role === 'admin') {
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        $tickets = $query->orderBy('created_at', 'desc')->get();
        return response()->json($tickets);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:general,technical,billing,shipping',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $ticket = Ticket::create([
            'user_id' => Auth::id(),
            'shop_id' => $request->shop_id ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'medium',
            'status' => 'open',
        ]);

        return response()->json([
            'message' => 'Ticket créé avec succès',
            'ticket' => $ticket,
        ], Response::HTTP_CREATED);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['user', 'shop', 'replies.user'])->find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ticket->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        return response()->json($ticket);
    }

    public function reply(Request $request, $id)
    {
        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($ticket->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'message' => 'required|string',
        ]);

        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $validated['message'],
            'is_admin' => Auth::user()->role === 'admin',
        ]);

        if (Auth::user()->role === 'admin') {
            $ticket->status = 'in_progress';
            $ticket->save();
        }

        return response()->json([
            'message' => 'Réponse ajoutée avec succès',
            'reply' => $reply,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return response()->json(['message' => 'Ticket non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $ticket->status = $validated['status'];
        $ticket->save();

        return response()->json([
            'message' => 'Statut mis à jour avec succès',
            'ticket' => $ticket,
        ]);
    }
}
