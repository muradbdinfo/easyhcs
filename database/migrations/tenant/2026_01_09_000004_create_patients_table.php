<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_no', 20)->unique(); // PT-2026-00001
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('date_of_birth')->nullable();
            $table->string('blood_group', 5)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('nid', 20)->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone', 20)->nullable();
            $table->enum('patient_type', ['regular', 'walkin'])->default('regular');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name', 'phone']);
            $table->index('patient_no');
            $table->index('patient_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};