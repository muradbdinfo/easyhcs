<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no', 30)->unique(); // INV-SAAS-2026-00001
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            $table->foreignId('subscription_id')->nullable()
                  ->constrained('subscriptions')->nullOnDelete();

            $table->foreignId('plan_id')->nullable()
                  ->constrained('plans')->nullOnDelete();

            // Amounts
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('due_amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('BDT');

            // Billing cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->date('billing_period_start')->nullable();
            $table->date('billing_period_end')->nullable();
            $table->date('due_date')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'paid', 'partial', 'overdue', 'cancelled', 'refunded'])
                  ->default('pending');

            // Payment
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_gateway')->nullable();   // sslcommerz|stripe|paypal|manual

            // PDF
            $table->string('pdf_path')->nullable();

            // Notes
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index('due_date');
            $table->index('invoice_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_invoices');
    }
};