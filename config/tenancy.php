<?php
declare(strict_types=1);
use Stancl\Tenancy\Database\Models\Domain;
return [
    'tenant_model' => \App\Models\System\Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,
    'domain_model' => Domain::class,

    'tenant_finder' => Stancl\Tenancy\TenantFinders\DomainTenantFinder::class,

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
        'tag_base' => 'tenant',
    ],

    'filesystem' => [
        'suffix_base'         => 'tenant',
        'disks'               => ['local', 'public'],
        'root_override'       => [
            'local' => '%storage_path%/app/',
        ],
        'suffix_storage_path' => true,
        'asset_helper_tenancy' => false,
    ],

    'redis' => [
        'prefix_base'          => 'tenant',
        'prefixed_connections' => ['default'],
    ],

    'features' => [
        Stancl\Tenancy\Features\UniversalRoutes::class,
    ],

    // ↓ relative path — works on Windows/Linux/Mac without backslash issues
    'migration_parameters' => [
        '--force'    => true,
        '--path'     => 'database/migrations/tenant',
        '--realpath' => false,
    ],

    'seeder_parameters' => [
        '--class' => 'TenantDatabaseSeeder',
        '--force' => true,
    ],

    // ↓ EMPTY = jobs run synchronously — no queue worker needed in dev
    'queue_actions' => [],
];