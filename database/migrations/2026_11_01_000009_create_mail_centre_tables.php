<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('email')->unique();
            $table->string('display_name');
            $table->string('inbound_host')->nullable();
            $table->unsignedSmallInteger('inbound_port')->nullable();
            $table->string('outbound_host')->nullable();
            $table->unsignedSmallInteger('outbound_port')->nullable();
            $table->string('encryption')->default('tls');
            $table->text('encrypted_credentials')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('mailbox_user', function (Blueprint $table): void {
            $table->foreignId('mailbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('can_send')->default(true);
            $table->timestamps();
            $table->primary(['mailbox_id', 'user_id']);
        });
        Schema::create('mail_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mailbox_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->string('external_id');
            $table->string('folder')->default('inbox');
            $table->string('from_address');
            $table->string('from_name')->nullable();
            $table->longText('to_addresses');
            $table->string('subject')->nullable();
            $table->text('preview')->nullable();
            $table->longText('body_html')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->unique(['mailbox_id', 'external_id']);
            $table->index(['mailbox_id', 'folder', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_messages');
        Schema::dropIfExists('mailbox_user');
        Schema::dropIfExists('mailboxes');
    }
};
