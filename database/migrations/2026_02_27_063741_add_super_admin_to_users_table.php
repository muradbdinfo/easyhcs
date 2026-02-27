<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The system DB users table (created by Breeze).
     * We add is_super_admin + 2FA fields here.
     * Note: This is the SYSTEM admin users table.
     * Tenant users live in each tenant DB (separate users table).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Super admin flag
            $table->boolean('is_super_admin')->default(false)->after('email_verified_at');

            // Two-factor authentication
            $table->string('two_factor_secret')->nullable()->after('is_super_admin');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');

            // Profile
            $table->string('phone', 20)->nullable()->after('two_factor_confirmed_at');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('timezone')->default('Asia/Dhaka')->after('avatar_path');

            // Login tracking
            $table->timestamp('last_login_at')->nullable()->after('timezone');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');

            // Status
            $table->boolean('is_active')->default(true)->after('last_login_ip');

            $table->index('is_super_admin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_super_admin',
                'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
                'phone', 'avatar_path', 'timezone',
                'last_login_at', 'last_login_ip',
                'is_active',
            ]);
        });
    }
};