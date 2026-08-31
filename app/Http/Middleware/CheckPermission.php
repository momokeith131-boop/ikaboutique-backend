<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->hasPermission($permission)) {
            return response()->json(['message' => 'Permission denied'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
