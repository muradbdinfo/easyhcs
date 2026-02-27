<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain as BaseMiddleware;

class InitializeTenancyByDomain
{
    public function __construct(protected BaseMiddleware $base) {}

    public function handle($request, Closure $next)
    {
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', []);

        // Skip tenancy for central domain (admin, auth, installer)
        if (in_array($host, $centralDomains)) {
            return $next($request);
        }

        return $this->base->handle($request, $next);
    }
}