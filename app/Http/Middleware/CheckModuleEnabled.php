<?php

namespace App\Http\Middleware;

use App\Models\Tenant\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleEnabled
{
    private const ALWAYS_ACTIVE = ['core', 'accounts'];

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (in_array($module, self::ALWAYS_ACTIVE)) {
            return $next($request);
        }

        $modules = Setting::get('modules_enabled', []);

        if (!in_array($module, (array) $modules)) {
            return response()->json([
                'message' => "Module '{$module}' is not enabled for this tenant.",
                'module'  => $module,
            ], 403);
        }

        return $next($request);
    }
}