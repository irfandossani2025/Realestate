<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('type');
            $table->string('status')->default('draft');
            $table->decimal('agreed_value', 14, 3);
            $table->decimal('deposit_amount', 14, 3)->default(0);
            $table->date('agreement_date')->nullable();
            $table->date('handover_date')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'type', 'status']);
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_transaction_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 14, 3);
            $table->string('method');
            $table->string('status')->default('pending');
            $table->date('received_on')->nullable();
            $table->string('proof_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('property_transactions');
    }
};
