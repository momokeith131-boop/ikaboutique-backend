<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JwtAuth\Facades\JwtAuth;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (!JwtAuth::parseToken()->authenticate()) {
                return response()->json([
                    'message' => 'User not authenticated',
                ], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token error: ' . $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
