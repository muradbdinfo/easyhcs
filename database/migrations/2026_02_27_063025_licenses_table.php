<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();

            $table->string('license_key', 100)->unique();
            $table->string('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->nullOnDelete();

            $table->foreignId('plan_id')->nullable()
                  ->constrained('plans')->nullOnDelete();

            // License type
            $table->enum('type', ['trial', 'standard', 'extended', 'lifetime', 'developer'])
                  ->default('standard');

            // Status
            $table->enum('status', ['unassigned', 'active', 'suspended', 'expired', 'revoked'])
                  ->default('unassigned');

            // Validity
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->string('revoke_reason')->nullable();

            // Heartbeat / validation tracking
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->string('last_heartbeat_ip', 45)->nullable();
            $table->string('fingerprint')->nullable();  // domain + server fingerprint
            $table->integer('failure_count')->default(0);
            $table->timestamp('last_failure_at')->nullable();

            // Max activations allowed
            $table->integer('max_activations')->default(1);
            $table->integer('activation_count')->default(0);

            // Who generated this license
            $table->unsignedBigInteger('generated_by')->nullable();

            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};