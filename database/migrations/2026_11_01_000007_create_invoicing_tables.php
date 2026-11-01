<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('commercial_registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->decimal('default_vat_rate', 5, 2)->default(5.00);
        });

        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('status')->default('draft');
            $table->string('currency', 3)->default('OMR');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 14, 3)->default(0);
            $table->decimal('vat_amount', 14, 3)->default(0);
            $table->decimal('total', 14, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'status', 'issue_date']);
        });

        Schema::create('invoice_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->string('description_ar')->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 14, 3);
            $table->decimal('vat_rate', 5, 2)->default(5.00);
            $table->decimal('subtotal', 14, 3);
            $table->decimal('vat_amount', 14, 3);
            $table->decimal('total', 14, 3);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn(['commercial_registration_number', 'vat_number', 'default_vat_rate']);
        });
    }
};
