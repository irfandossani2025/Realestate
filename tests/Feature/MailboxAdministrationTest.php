<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailboxAdministrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_encrypts_and_assigns_mailbox_credentials(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $admin = User::factory()->create();
        $employee = User::factory()->create();
        $admin->companies()->attach($company, ['role' => 'admin', 'is_active' => true]);
        $employee->companies()->attach($company, ['role' => 'sales_agent', 'is_active' => true]);

        $this->actingAs($admin)->post(route('mail.settings.store'), [
            'company_id' => $company->id, 'user_id' => $employee->id, 'email' => 'sales@alramzinternational.com',
            'display_name' => 'Sales', 'inbound_host' => 'mail.alramzinternational.com', 'inbound_port' => 993,
            'outbound_host' => 'mail.alramzinternational.com', 'outbound_port' => 465, 'encryption' => 'ssl',
            'username' => 'sales@alramzinternational.com', 'password' => 'mailbox-secret',
        ])->assertRedirectToRoute('mail.settings');

        $mailbox = Mailbox::query()->firstOrFail();
        $this->assertSame('mailbox-secret', $mailbox->encrypted_credentials['password']);
        $this->assertStringNotContainsString('mailbox-secret', (string) $mailbox->getRawOriginal('encrypted_credentials'));
        $this->assertTrue($employee->mailboxes()->whereKey($mailbox->id)->exists());
    }
}
