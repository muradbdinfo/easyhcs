<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PDO;
use PDOException;
use Throwable;

class InstallerService
{
    // ─── Step 1: Requirements ────────────────────────────────────────────────

    public function checkRequirements(): array
    {
        $phpVersion = phpversion();
        $phpOk      = version_compare($phpVersion, '8.2.0', '>=');

        $extensions = [
            'curl', 'fileinfo', 'gd', 'intl',
            'mbstring', 'openssl', 'pdo_mysql', 'zip', 'exif',
        ];

        $extResults = [];
        foreach ($extensions as $ext) {
            $extResults[$ext] = extension_loaded($ext);
        }

        $paths = [
            storage_path()               => 'storage/',
            storage_path('logs')         => 'storage/logs/',
            storage_path('framework')    => 'storage/framework/',
            base_path('bootstrap/cache') => 'bootstrap/cache/',
        ];

        $writeResults = [];
        foreach ($paths as $path => $label) {
            $writeResults[$label] = is_writable($path);
        }

        $allPassed = $phpOk
            && !in_array(false, $extResults, true)
            && !in_array(false, $writeResults, true);

        return [
            'php_version' => $phpVersion,
            'php_ok'      => $phpOk,
            'extensions'  => $extResults,
            'writable'    => $writeResults,
            'all_passed'  => $allPassed,
        ];
    }

    // ─── Step 2: Database ────────────────────────────────────────────────────

    public function testDatabase(array $data): array
    {
        try {
            $password = $data['password'] ?? '';
            $dsn      = "mysql:host={$data['host']};port={$data['port']};dbname={$data['database']};charset=utf8mb4";
            $pdo      = new PDO($dsn, $data['username'], $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $grants    = $pdo->query("SHOW GRANTS FOR CURRENT_USER()")->fetchAll(PDO::FETCH_COLUMN);
            $hasCreate = collect($grants)->some(fn($g) =>
                str_contains($g, 'ALL PRIVILEGES') || str_contains($g, 'CREATE')
            );

            return [
                'success'    => true,
                'has_create' => $hasCreate,
                'message'    => 'Connection successful.',
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    // ─── Step 6: Run Full Install ────────────────────────────────────────────

    public function install(array $data): array
    {
        try {
            // 1. Write .env with freshly generated APP_KEY (no \r\n issues)
            $this->writeEnv($data);

            // 2. Run system DB migrations
            Artisan::call('migrate', [
                '--force' => true,
                '--path'  => 'database/migrations',
            ]);

            // 3. Seed system data
            Artisan::call('db:seed', [
                '--class' => 'SystemSeeder',
                '--force' => true,
            ]);

            // 4. Create super-admin user
            $this->createSuperAdmin($data['admin']);

            // 5. Storage link
            try {
                Artisan::call('storage:link');
            } catch (Throwable) {}

            // 6. Write installed.lock
            file_put_contents(storage_path('installed.lock'), now()->toIso8601String());

            return ['success' => true, 'message' => 'EasyHCS installed successfully.'];

        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function writeEnv(array $data): void
    {
        $db   = $data['database'];
        $app  = $data['app'];
        $mail = $data['mail'];

        $dbPassword   = $db['password']   ?? '';
        $mailUser     = $mail['username'] ?? '';
        $mailPassword = $mail['password'] ?? '';

        // Generate valid AES-256 key directly
        $key = 'base64:' . base64_encode(random_bytes(32));

        // Use \n (Unix line endings) — avoids \r contaminating values on Windows
        $lines = [
            'APP_NAME="' . $app['name'] . '"',
            'APP_ENV=local',
            'APP_KEY=' . $key,
            'APP_DEBUG=true',
            'APP_URL=' . $app['url'],
            'APP_TIMEZONE=' . $app['timezone'],
            '',
            'LOG_CHANNEL=stack',
            'LOG_LEVEL=debug',
            '',
            'DB_CONNECTION=mysql',
            'DB_HOST=' . $db['host'],
            'DB_PORT=' . $db['port'],
            'DB_DATABASE=' . $db['database'],
            'DB_USERNAME=' . $db['username'],
            'DB_PASSWORD=' . $dbPassword,
            '',
            'CACHE_STORE=file',
            'QUEUE_CONNECTION=database',
            'SESSION_DRIVER=file',
            '',
            'MAIL_MAILER=' . $mail['mailer'],
            'MAIL_HOST=' . $mail['host'],
            'MAIL_PORT=' . $mail['port'],
            'MAIL_USERNAME=' . $mailUser,
            'MAIL_PASSWORD=' . $mailPassword,
            'MAIL_ENCRYPTION=' . $mail['encryption'],
            'MAIL_FROM_ADDRESS=' . $mail['from_address'],
            'MAIL_FROM_NAME="' . $app['name'] . '"',
            '',
            'TENANCY_DATABASE_PREFIX=tenant_',
        ];

        // Join with Unix \n — no \r\n on Windows
        $env = implode("\n", $lines) . "\n";

        file_put_contents(base_path('.env'), $env);

        // Inject key into running process
        config(['app.key' => $key]);

        Artisan::call('config:clear');
    }

    private function createSuperAdmin(array $admin): void
    {
        DB::reconnect();

        \App\Models\System\AdminUser::firstOrCreate(
            ['email' => $admin['email']],
            [
                'name'           => $admin['name'],
                'password'       => Hash::make($admin['password']),
                'is_super_admin' => true,
                'is_active'      => true,
            ]
        );
    }
}