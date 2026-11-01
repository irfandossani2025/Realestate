<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('viewings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->timestamp('scheduled_at');
            $table->string('status')->default('scheduled');
            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('viewings');
    }
};
