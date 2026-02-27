<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        // Clear PHP stat cache — fixes Windows XAMPP file_exists() caching bug
        clearstatcache(true, storage_path('installed.lock'));

        $installed = file_exists(storage_path('installed.lock'));

        if ($request->is('install*') && $installed) {
            return redirect('/login');
        }

        if (!$request->is('install*') && !$installed) {
            return redirect('/install');
        }

        return $next($request);
    }
}