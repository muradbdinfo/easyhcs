<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();

            $table->string('name', 50)->unique();           // SSLCommerz | Stripe | PayPal
            $table->string('slug', 30)->unique();           // sslcommerz | stripe | paypal
            $table->string('display_name', 100)->nullable();
            $table->text('description')->nullable();

            // Credentials (encrypted)
            $table->text('config')->nullable(); // JSON: store_id, store_pass, api_key etc (encrypted)

            // Mode
            $table->enum('mode', ['sandbox', 'live'])->default('sandbox');

            // Supported currencies
            $table->json('supported_currencies')->default('["BDT"]');

            // Supported payment methods via this gateway
            $table->json('supported_methods')->default('["card"]');

            // Features
            $table->boolean('supports_refund')->default(false);
            $table->boolean('supports_recurring')->default(false);
            $table->boolean('supports_webhook')->default(false);

            // Status
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);

            // Fees
            $table->decimal('fee_percentage', 5, 2)->default(0);
            $table->decimal('fee_fixed', 8, 2)->default(0);
            $table->string('fee_currency', 3)->default('BDT');

            // Webhook
            $table->string('webhook_secret')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};