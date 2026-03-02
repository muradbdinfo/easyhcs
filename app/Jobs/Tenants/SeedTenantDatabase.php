<?php

namespace App\Jobs\Tenants;

use App\Models\Tenant\Setting;
use App\Models\Tenant\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class SeedTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected TenantWithDatabase $tenant) {}

    public function handle(): void
    {
        // Roles seeder
        $this->seedRolesAndPermissions();

        // Default settings
        $this->seedSettings();

        // Create tenant owner from system tenant data
        $this->createOwner();
    }

    private function seedRolesAndPermissions(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ─── All permissions ───
        $permissions = [
            // Core
            'view-dashboard', 'manage-users', 'manage-roles', 'manage-settings',
            'view-audit-log', 'manage-patients',
            // Pharmacy
            'pharmacy-sell', 'pharmacy-view-sales', 'pharmacy-manage-medicines',
            'pharmacy-manage-categories', 'pharmacy-manage-suppliers',
            'pr-create', 'pr-view-own', 'pr-view-all',
            'pr-approve-step1', 'pr-approve-step2', 'pr-reject', 'pr-cancel',
            'po-create', 'po-view', 'po-receive', 'po-manage',
            'pharmacy-stock-adjust', 'pharmacy-view-reports',
            // Diagnostic
            'diagnostic-create-order', 'diagnostic-view-orders', 'diagnostic-collect-sample',
            'diagnostic-enter-result', 'diagnostic-verify-result', 'diagnostic-release-result',
            'diagnostic-manage-tests', 'diagnostic-view-reports',
            // Hospital
            'hospital-manage-doctors', 'hospital-appointments', 'hospital-emr',
            'hospital-prescribe', 'hospital-admissions', 'hospital-ward-management',
            'hospital-ot', 'hospital-billing', 'hospital-insurance', 'hospital-discharge',
            'hospital-view-reports',
            // Accounts
            'accounts-manage', 'accounts-journals', 'accounts-expenses',
            'accounts-view-reports', 'accounts-manage-dues',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'tenant']);
        }

        // ─── Roles + their permissions ───
        $roleMap = [
            'tenant-owner' => $permissions, // All
            'admin' => array_diff($permissions, []), // All (same as owner for permissions)
            'manager' => [
                'view-dashboard', 'manage-patients',
                'pr-view-all', 'pr-approve-step1', 'pr-reject',
                'pharmacy-view-reports', 'po-view', 'po-create',
                'hospital-appointments', 'accounts-view-reports',
                'diagnostic-view-orders', 'diagnostic-view-reports',
            ],
            'doctor' => [
                'view-dashboard', 'manage-patients',
                'hospital-appointments', 'hospital-emr', 'hospital-prescribe',
                'hospital-admissions', 'diagnostic-view-orders',
            ],
            'nurse' => [
                'view-dashboard', 'manage-patients',
                'hospital-emr', 'hospital-admissions', 'hospital-ward-management',
                'diagnostic-collect-sample',
            ],
            'cashier' => [
                'view-dashboard', 'manage-patients',
                'pharmacy-sell', 'pharmacy-view-sales',
                'diagnostic-create-order', 'diagnostic-view-orders',
                'hospital-billing', 'accounts-manage-dues',
            ],
            'staff' => [
                'view-dashboard', 'manage-patients',
                'pr-create', 'pr-view-own', 'pr-cancel',
                'pharmacy-manage-medicines',
                'diagnostic-view-orders',
            ],
        ];

        foreach ($roleMap as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'tenant']);
            $role->syncPermissions($perms);
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            ['key' => 'clinic_name',        'value' => $this->tenant->name ?? 'My Clinic',  'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'clinic_address',     'value' => '',                                   'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'clinic_phone',       'value' => '',                                   'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'clinic_email',       'value' => '',                                   'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'currency',           'value' => 'BDT',                                'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'currency_symbol',    'value' => '৳',                                  'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'timezone',           'value' => 'Asia/Dhaka',                          'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'date_format',        'value' => 'd/m/Y',                              'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'logo',               'value' => null,                                 'type' => 'string',  'group' => 'general', 'is_public' => true],
            ['key' => 'modules_enabled',    'value' => json_encode(['accounts']),             'type' => 'json',    'group' => 'modules', 'is_public' => true],
            ['key' => 'payment_methods',    'value' => json_encode(['cash']),                 'type' => 'json',    'group' => 'payment', 'is_public' => true],
            ['key' => 'two_factor_required','value' => '0',                                  'type' => 'boolean', 'group' => 'security','is_public' => false],
        ];

        foreach ($defaults as $row) {
            Setting::firstOrCreate(['key' => $row['key']], $row);
        }
    }

    private function createOwner(): void
    {
        // Pull owner data from central tenant record data column
        $ownerData = $this->tenant->owner ?? null;
        if (!$ownerData) {
            return;
        }

        $user = User::create([
            'name'              => $ownerData['name'],
            'email'             => $ownerData['email'],
            'password'          => Hash::make($ownerData['password']),
            'status'            => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('tenant-owner');
    }
}