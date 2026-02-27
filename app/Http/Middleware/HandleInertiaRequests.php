<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Share data with every Inertia response.
     * Keep this lean — only share what every page needs.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return array_merge(parent::share($request), [

            // Auth user + permissions
            'auth' => [
                'user' => $user ? [
                    'id'             => $user->id,
                    'name'           => $user->name,
                    'email'          => $user->email,
                    'avatar'         => $user->avatar_path
                        ? asset('storage/' . $user->avatar_path)
                        : null,
                    'is_super_admin' => (bool) $user->is_super_admin,
                    'roles'          => $user->getRoleNames()->toArray(),
                    'permissions'    => $user->getPermissionsViaRoles()->pluck('name')->toArray(),
                ] : null,
            ],

            // Flash messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info'    => fn () => $request->session()->get('info'),
            ],

            // Tenant context (null on admin/central routes)
            'tenant' => fn () => $this->getTenantData(),

            // Ziggy routes for Vue
            'ziggy' => fn () => array_merge(
                (new Ziggy)->toArray(),
                ['location' => $request->url()]
            ),
        ]);
    }

    private function getTenantData(): ?array
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant) return null;

            return [
                'id'              => $tenant->id,
                'name'            => $tenant->business_name,
                'plan'            => $tenant->plan?->name,
                'plan_status'     => $tenant->plan_status,
                'modules_enabled' => $tenant->getEnabledModules(),
                'currency'        => $tenant->currency ?? 'BDT',
                'timezone'        => $tenant->timezone ?? 'Asia/Dhaka',
                'logo'            => $tenant->logo_path
                    ? asset('storage/' . $tenant->logo_path)
                    : null,
                'trial_ends_at'   => $tenant->trial_ends_at?->toDateString(),
                'plan_expires_at' => $tenant->plan_expires_at?->toDateString(),
            ];
        } catch (\Exception) {
            return null;
        }
    }
}