<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * stancl/tenancy creates the base `tenants` table.
     * This migration adds EasyHCS-specific columns.
     * Run AFTER tenancy:install migration.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Clinic / Business info
            $table->string('business_name')->nullable()->after('id');
            $table->string('business_type')->nullable()->after('business_name'); // clinic|pharmacy|hospital|diagnostic
            $table->string('contact_email')->nullable()->after('business_type');
            $table->string('contact_phone', 20)->nullable()->after('contact_email');
            $table->text('address')->nullable()->after('contact_phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('country', 100)->default('Bangladesh')->after('city');
            $table->string('timezone')->default('Asia/Dhaka')->after('country');
            $table->string('currency', 3)->default('BDT')->after('timezone');
            $table->string('logo_path')->nullable()->after('currency');

            // Plan & subscription
            $table->foreignId('plan_id')->nullable()->after('logo_path')
                  ->constrained('plans')->nullOnDelete();
            $table->enum('plan_status', ['trialing', 'active', 'past_due', 'suspended', 'cancelled'])
                  ->default('trialing')->after('plan_id');
            $table->timestamp('trial_ends_at')->nullable()->after('plan_status');
            $table->timestamp('plan_expires_at')->nullable()->after('trial_ends_at');

            // Owner
            $table->unsignedBigInteger('owner_user_id')->nullable()->after('plan_expires_at');
            // No FK — tenant owner lives in tenant DB

            // Status
            $table->boolean('is_active')->default(true)->after('owner_user_id');
            $table->boolean('email_verified')->default(false)->after('is_active');
            $table->timestamp('activated_at')->nullable()->after('email_verified');
            $table->timestamp('suspended_at')->nullable()->after('activated_at');
            $table->text('suspension_reason')->nullable()->after('suspended_at');

            // License
            $table->string('license_key', 100)->nullable()->unique()->after('suspension_reason');
            $table->integer('license_failure_count')->default(0)->after('license_key');
            $table->timestamp('license_last_check')->nullable()->after('license_failure_count');

            // Modules enabled (override from plan)
            $table->json('modules_enabled')->nullable()->after('license_last_check');

            // Usage tracking
            $table->integer('user_count')->default(0)->after('modules_enabled');
            $table->integer('patient_count')->default(0)->after('user_count');
            $table->integer('storage_used_mb')->default(0)->after('patient_count');

            // Metadata
            $table->json('meta')->nullable()->after('storage_used_mb');
            $table->softDeletes();

            // Indexes
            $table->index('plan_id');
            $table->index('plan_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'business_name', 'business_type', 'contact_email', 'contact_phone',
                'address', 'city', 'country', 'timezone', 'currency', 'logo_path',
                'plan_id', 'plan_status', 'trial_ends_at', 'plan_expires_at',
                'owner_user_id', 'is_active', 'email_verified', 'activated_at',
                'suspended_at', 'suspension_reason', 'license_key',
                'license_failure_count', 'license_last_check', 'modules_enabled',
                'user_count', 'patient_count', 'storage_used_mb', 'meta',
            ]);
        });
    }
};