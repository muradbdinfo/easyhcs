<?php

declare(strict_types=1);

// use App\Models\System\Tenant;
use Stancl\Tenancy\Database\Models\Domain;


return [

   'tenant_model' => 'App\Models\System\Tenant',
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,
    'domain_model' => Domain::class,

    /**
     * Use PathTenantFinder in XAMPP dev (avoids hosts file per tenant).
     * Switch to SubdomainTenantFinder on cPanel/VPS.
     *
     * Path: http://easyhcs.local/demo/dashboard  → tenant slug = demo
     * Subdomain: http://demo.easyhcs.local/dashboard → tenant = demo
     */
    'tenant_finder' => Stancl\Tenancy\TenantFinders\DomainTenantFinder::class,
    // For XAMPP path-based dev, change to:
    // 'tenant_finder' => Stancl\Tenancy\TenantFinders\PathTenantFinder::class,

    'central_domains' => [
        env('CENTRAL_DOMAIN', 'easyhcs.local'),
    ],

    'bootstrappers' => [
        Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],

    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'tenant_connection'  => 'tenant',
        'prefix'             => env('TENANCY_DATABASE_PREFIX', 'tenant_'),
        'suffix'             => '',
        'managers'           => [
            'sqlite' => Stancl\Tenancy\Database\DatabaseManager::class,
            'mysql'  => Stancl\Tenancy\Database\DatabaseManager::class,
        ],
    ],

    'cache' => [
        'tag_base' => 'tenant',  // cache key prefix per tenant
    ],

    'filesystem' => [
        'suffix_base'  => 'tenant',
        'disks'        => ['local', 'public'],
        'root_override' => [
            'local' => '%storage_path%/app/',
        ],
        'suffix_storage_path' => true,
        'asset_helper_tenancy' => false,
    ],

    'redis' => [
        'prefix_base' => 'tenant',
        'prefixed_connections' => ['default'],
    ],

    'features' => [
        // Stancl\Tenancy\Features\UserImpersonation::class,
        // Stancl\Tenancy\Features\TelescopeTags::class,
        Stancl\Tenancy\Features\UniversalRoutes::class,
        // Stancl\Tenancy\Features\TenantConfig::class,
        // Stancl\Tenancy\Features\CrossDomainRedirect::class,
        // Stancl\Tenancy\Features\ViteBundler::class,
    ],

    'migration_parameters' => [
        '--force'   => true,
        '--path'    => database_path('migrations/tenant'),
        '--realpath' => true,
    ],

    'seeder_parameters' => [
        '--class' => 'TenantDatabaseSeeder',
        '--force' => true,
    ],

    'queue_actions' => [
        Stancl\Tenancy\Jobs\CreateDatabase::class,
        Stancl\Tenancy\Jobs\MigrateDatabase::class,
        Stancl\Tenancy\Jobs\SeedDatabase::class,
        Stancl\Tenancy\Jobs\DeleteDatabase::class,
    ],
];