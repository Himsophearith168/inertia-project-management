<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('projects');

        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'name']);
        });

        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('room_number', 40);
            $table->string('floor', 40)->nullable();
            $table->decimal('monthly_rent', 12, 2)->default(0);
            $table->string('status', 20)->default('vacant');
            $table->date('next_rent_due_date')->nullable();
            $table->decimal('electricity_start_reading', 12, 2)->default(0);
            $table->decimal('water_start_reading', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'room_number']);
            $table->unique('tenant_id');
            $table->index(['user_id', 'status', 'next_rent_due_date']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('paid_on');
            $table->string('method', 30);
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['room_id', 'paid_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('tenants');

        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('client');
            $table->string('status')->default('planning');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};
