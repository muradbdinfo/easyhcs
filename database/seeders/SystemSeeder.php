<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SystemSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPlans();
        $this->seedPaymentGateways();
        $this->seedSuperAdmin();

        $this->command->info('✅ SystemSeeder completed.');
    }

    // ─────────────────────────────────────────────────────────────
    // PLANS
    // ─────────────────────────────────────────────────────────────
    private function seedPlans(): void
    {
        $plans = [
            [
                'name'             => 'Pharmacy Basic',
                'slug'             => 'pharmacy-basic',
                'description'      => 'Perfect for standalone pharmacies. Includes POS, inventory, purchase management.',
                'price_monthly'    => 1500.00,
                'price_yearly'     => 15000.00,
                'currency'         => 'BDT',
                'modules_enabled'  => json_encode(['core', 'pharmacy', 'accounts']),
                'plan_type'        => 'pharmacy',
                'max_users'        => 5,
                'max_patients'     => 0,  // no patients module for pharmacy only
                'max_sms_per_month' => 0,
                'max_storage_mb'   => 500,
                'has_api_access'   => false,
                'has_custom_domain' => false,
                'has_white_label'  => false,
                'has_priority_support' => false,
                'has_backup'       => false,
                'trial_days'       => 14,
                'is_active'        => true,
                'is_featured'      => false,
                'sort_order'       => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Diagnostic Center',
                'slug'             => 'diagnostic',
                'description'      => 'For diagnostic labs and imaging centers. Test orders, results, sample tracking.',
                'price_monthly'    => 2000.00,
                'price_yearly'     => 20000.00,
                'currency'         => 'BDT',
                'modules_enabled'  => json_encode(['core', 'diagnostic', 'accounts']),
                'plan_type'        => 'diagnostic',
                'max_users'        => 10,
                'max_patients'     => 5000,
                'max_sms_per_month' => 100,
                'max_storage_mb'   => 1024,
                'has_api_access'   => false,
                'has_custom_domain' => false,
                'has_white_label'  => false,
                'has_priority_support' => false,
                'has_backup'       => true,
                'trial_days'       => 14,
                'is_active'        => true,
                'is_featured'      => false,
                'sort_order'       => 2,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Hospital Standard',
                'slug'             => 'hospital-standard',
                'description'      => 'For small hospitals and clinics. IPD, OPD, appointments, EMR, prescriptions.',
                'price_monthly'    => 4000.00,
                'price_yearly'     => 40000.00,
                'currency'         => 'BDT',
                'modules_enabled'  => json_encode(['core', 'hospital', 'pharmacy', 'accounts']),
                'plan_type'        => 'hospital',
                'max_users'        => 20,
                'max_patients'     => -1,  // unlimited
                'max_sms_per_month' => 500,
                'max_storage_mb'   => 5120,
                'has_api_access'   => true,
                'has_custom_domain' => false,
                'has_white_label'  => false,
                'has_priority_support' => true,
                'has_backup'       => true,
                'trial_days'       => 14,
                'is_active'        => true,
                'is_featured'      => true,
                'sort_order'       => 3,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'name'             => 'Full Suite',
                'slug'             => 'full',
                'description'      => 'Everything included. Pharmacy + Diagnostic + Hospital + Accounts. Unlimited users.',
                'price_monthly'    => 8000.00,
                'price_yearly'     => 80000.00,
                'currency'         => 'BDT',
                'modules_enabled'  => json_encode(['core', 'pharmacy', 'diagnostic', 'hospital', 'accounts']),
                'plan_type'        => 'full',
                'max_users'        => -1,  // unlimited
                'max_patients'     => -1,
                'max_sms_per_month' => -1,
                'max_storage_mb'   => -1,
                'has_api_access'   => true,
                'has_custom_domain' => true,
                'has_white_label'  => true,
                'has_priority_support' => true,
                'has_backup'       => true,
                'trial_days'       => 30,
                'is_active'        => true,
                'is_featured'      => false,
                'sort_order'       => 4,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('plans')->updateOrInsert(
                ['slug' => $plan['slug']],
                $plan
            );
        }

        $this->command->info('  Plans seeded (' . count($plans) . ')');
    }

    // ─────────────────────────────────────────────────────────────
    // PAYMENT GATEWAYS
    // ─────────────────────────────────────────────────────────────
    private function seedPaymentGateways(): void
    {
        $gateways = [
            [
                'name'                 => 'SSLCommerz',
                'slug'                 => 'sslcommerz',
                'display_name'         => 'SSLCommerz (Bangladesh)',
                'description'          => 'Popular Bangladeshi payment gateway. Supports bKash, Nagad, card, net banking.',
                'config'               => null,  // Set via Admin panel
                'mode'                 => 'sandbox',
                'supported_currencies' => json_encode(['BDT']),
                'supported_methods'    => json_encode(['card', 'bkash', 'nagad', 'bank_transfer']),
                'supports_refund'      => true,
                'supports_recurring'   => false,
                'supports_webhook'     => true,
                'is_active'            => false,
                'is_default'           => true,
                'fee_percentage'       => 2.50,
                'fee_fixed'            => 0.00,
                'fee_currency'         => 'BDT',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
            [
                'name'                 => 'Stripe',
                'slug'                 => 'stripe',
                'display_name'         => 'Stripe (International)',
                'description'          => 'International card payments. Supports Visa, Mastercard, AMEX.',
                'config'               => null,
                'mode'                 => 'sandbox',
                'supported_currencies' => json_encode(['USD', 'EUR', 'GBP', 'BDT']),
                'supported_methods'    => json_encode(['card']),
                'supports_refund'      => true,
                'supports_recurring'   => true,
                'supports_webhook'     => true,
                'is_active'            => false,
                'is_default'           => false,
                'fee_percentage'       => 2.90,
                'fee_fixed'            => 0.30,
                'fee_currency'         => 'USD',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
            [
                'name'                 => 'PayPal',
                'slug'                 => 'paypal',
                'display_name'         => 'PayPal',
                'description'          => 'PayPal checkout. Accepts PayPal balance and linked cards.',
                'config'               => null,
                'mode'                 => 'sandbox',
                'supported_currencies' => json_encode(['USD', 'EUR', 'GBP']),
                'supported_methods'    => json_encode(['paypal', 'card']),
                'supports_refund'      => true,
                'supports_recurring'   => true,
                'supports_webhook'     => true,
                'is_active'            => false,
                'is_default'           => false,
                'fee_percentage'       => 3.40,
                'fee_fixed'            => 0.30,
                'fee_currency'         => 'USD',
                'created_at'           => now(),
                'updated_at'           => now(),
            ],
        ];

        foreach ($gateways as $gateway) {
            DB::table('payment_gateways')->updateOrInsert(
                ['slug' => $gateway['slug']],
                $gateway
            );
        }

        $this->command->info('  Payment gateways seeded (' . count($gateways) . ')');
    }

    // ─────────────────────────────────────────────────────────────
    // SUPER ADMIN
    // ─────────────────────────────────────────────────────────────
    private function seedSuperAdmin(): void
    {
        // We store super admin in system DB users table
        // This requires a User model pointing to system DB
        // Using direct DB insert to avoid dependency issues at seeder stage
        $exists = DB::table('users')->where('email', 'admin@easyhcs.com')->first();

        if (!$exists) {
            DB::table('users')->insert([
                'name'              => 'EasyHCS Admin',
                'email'             => 'admin@easyhcs.com',
                'password'          => Hash::make('Admin@2026!'),
                'is_super_admin'    => true,
                'email_verified_at' => now(),
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $this->command->info('  Super admin created: admin@easyhcs.com / Admin@2026!');
        } else {
            $this->command->warn('  Super admin already exists — skipped.');
        }
    }
}