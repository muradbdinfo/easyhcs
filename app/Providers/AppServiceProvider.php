<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Force stancl to use our custom Tenant model everywhere
        $this->app->bind(
            \Stancl\Tenancy\Database\Models\Tenant::class,
            \App\Models\System\Tenant::class
        );

        $this->app->bind(
            \Stancl\Tenancy\Contracts\Tenant::class,
            \App\Models\System\Tenant::class
        );


    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}