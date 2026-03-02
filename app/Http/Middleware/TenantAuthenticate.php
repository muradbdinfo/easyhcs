<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth('tenant')->check()) {
            if ($request->expectsJson()) {
                throw new AuthenticationException('Unauthenticated.');
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}