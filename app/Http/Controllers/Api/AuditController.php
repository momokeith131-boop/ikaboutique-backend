<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuditController
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $query = AuditLog::with('user');

        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        if ($request->has('entity')) {
            $query->where('entity', $request->entity);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    public function show($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $log = AuditLog::with('user')->find($id);

        if (!$log) {
            return response()->json(['message' => 'Log non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($log);
    }

    public function userLogs($userId)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $logs = AuditLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($logs);
    }

    public function stats()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_FORBIDDEN);
        }

        $stats = [
            'total' => AuditLog::count(),
            'by_action' => AuditLog::groupBy('action')->selectRaw('action, count(*) as count')->get(),
            'by_entity' => AuditLog::groupBy('entity')->selectRaw('entity, count(*) as count')->get(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'this_week' => AuditLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => AuditLog::whereMonth('created_at', now()->month)->count(),
        ];

        return response()->json($stats);
    }
}
