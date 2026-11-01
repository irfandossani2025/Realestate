<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('valuation_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('evaluator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference')->unique();
            $table->string('purpose');
            $table->string('status')->default('requested');
            $table->decimal('fee', 14, 3)->nullable();
            $table->timestamp('inspection_at')->nullable();
            $table->timestamp('report_due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['evaluator_id', 'inspection_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('valuation_requests');
    }
};
