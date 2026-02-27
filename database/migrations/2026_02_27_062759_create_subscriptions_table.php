<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();

            // Billing cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');

            // Dates
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('renewed_at')->nullable();

            // Status
            $table->enum('status', [
                'trialing', 'active', 'past_due', 'suspended', 'cancelled', 'expired'
            ])->default('trialing');

            // Renewal
            $table->boolean('auto_renew')->default(true);
            $table->integer('grace_period_days')->default(7);

            // Dunning (overdue reminder tracking)
            $table->integer('dunning_count')->default(0);
            $table->timestamp('dunning_last_sent_at')->nullable();

            // Snapshot of plan modules at subscription time
            $table->json('modules_snapshot')->nullable();

            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('ends_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};