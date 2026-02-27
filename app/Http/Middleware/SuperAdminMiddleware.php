<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->is_super_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Access denied. Super admin only.'], 403);
            }
            return redirect()->route('login')
                ->with('error', 'Access denied. Super admin privileges required.');
        }

        return $next($request);
    }
}