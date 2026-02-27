<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseMiddleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wraps stancl's InitializeTenancyByDomain.
 * Skips tenancy initialization on central domain (admin + auth routes).
 */
class InitializeTenancyByDomain extends BaseMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        // Skip tenancy for central domain (admin panel, installer, auth)
        if (in_array($host, $centralDomains)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}