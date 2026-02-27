<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * System DB — NOT tenant DB.
     * Stores SaaS subscription plans sold to clinic owners.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Basic | Professional | Enterprise
            $table->string('slug')->unique();               // basic | professional | enterprise
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->string('currency', 3)->default('BDT'); // BDT | USD

            // Modules enabled for this plan (JSON array)
            // e.g. ["core","pharmacy","accounts"]
            $table->json('modules_enabled')->default('["core","accounts"]');

            // Limits
            $table->integer('max_users')->default(5);
            $table->integer('max_patients')->default(1000);   // -1 = unlimited
            $table->integer('max_sms_per_month')->default(0); // -1 = unlimited
            $table->integer('max_storage_mb')->default(500);  // -1 = unlimited

            // Features flags
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_custom_domain')->default(false);
            $table->boolean('has_white_label')->default(false);
            $table->boolean('has_priority_support')->default(false);
            $table->boolean('has_backup')->default(false);

            // Plan type
            $table->enum('plan_type', ['pharmacy', 'diagnostic', 'hospital', 'full', 'custom'])
                  ->default('pharmacy');

            // Trial
            $table->integer('trial_days')->default(14);

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            // Soft delete + timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};