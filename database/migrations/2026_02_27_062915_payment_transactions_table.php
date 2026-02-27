<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Context A — SaaS billing payments (tenant → EasyHCS).
     * NOT for patient payments inside clinic (that's tenant DB).
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('transaction_no', 40)->unique(); // TXN-2026-00001
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->foreignId('invoice_id')->nullable()
                  ->constrained('subscription_invoices')->nullOnDelete();

            // Gateway info
            $table->enum('gateway', ['sslcommerz', 'stripe', 'paypal', 'manual'])
                  ->default('manual');
            $table->string('gateway_transaction_id')->nullable(); // External TXN ID
            $table->json('gateway_response')->nullable();         // Raw gateway response

            // Payment details
            $table->enum('payment_method', ['card', 'bkash', 'nagad', 'bank_transfer', 'cash', 'other'])
                  ->default('card');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BDT');
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->decimal('net_amount', 10, 2)->default(0);

            // Status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded', 'cancelled'])
                  ->default('pending');

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->string('failure_reason')->nullable();

            // Refund
            $table->boolean('is_refunded')->default(false);
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->text('refund_reason')->nullable();
            $table->unsignedBigInteger('refunded_by')->nullable();
            // No FK on refunded_by — system admin lives in system DB users concept
            // but here we just store admin ID reference

            // IPN / Webhook data
            $table->string('ipn_validation_id')->nullable();
            $table->boolean('ipn_verified')->default(false);

            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('gateway');
            $table->index('status');
            $table->index('gateway_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};