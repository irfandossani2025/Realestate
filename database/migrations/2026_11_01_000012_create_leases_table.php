<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_transaction_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->date('starts_on');
            $table->date('ends_on');
            $table->decimal('rent_amount', 14, 3);
            $table->string('rent_frequency')->default('monthly');
            $table->decimal('security_deposit', 14, 3)->default(0);
            $table->date('next_payment_due_on')->nullable();
            $table->unsignedSmallInteger('renewal_notice_days')->default(60);
            $table->string('renewal_status')->default('not_due');
            $table->string('status')->default('active');
            $table->timestamps();
            $table->index(['company_id', 'status', 'ends_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leases');
    }
};
