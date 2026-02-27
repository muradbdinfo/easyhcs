<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        // Get tenant (set by tenancy bootstrapper)
        $tenant = tenancy()->tenant;

        if (!$tenant) {
            abort(403, 'Tenant context not found.');
        }

        $enabledModules = $tenant->getEnabledModules();

        if (!in_array($module, $enabledModules)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Module '{$module}' is not enabled on your current plan.",
                    'module'  => $module,
                    'upgrade' => true,
                ], 403);
            }

            // Inertia redirect to module locked page
            return redirect()->route('module.locked', ['module' => $module]);
        }

        return $next($request);
    }
}