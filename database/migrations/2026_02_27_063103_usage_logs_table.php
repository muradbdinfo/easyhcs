<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // What was tracked
            $table->string('metric', 50); // user_count|patient_count|sms_count|api_calls|storage_mb
            $table->unsignedInteger('value')->default(0);
            $table->unsignedInteger('limit')->default(0);   // 0 = unlimited
            $table->string('period', 10)->nullable();       // 2026-01 (monthly) or 2026 (yearly)

            $table->json('meta')->nullable();
            $table->timestamp('logged_at')->useCurrent();

            // Indexes
            $table->index('tenant_id');
            $table->index(['tenant_id', 'metric', 'period']);
            $table->index('logged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};