<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // Allow permission checks via Gate using Spatie roles/permissions for tenant guard
        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasPermissionTo')) {
                if ($user->hasPermissionTo($ability)) {
                    return true;
                }
            }
        });
    }
}