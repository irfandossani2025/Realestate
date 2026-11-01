<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Mailbox;
use App\Models\MailMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MailCentreAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_messages_from_assigned_mailboxes(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $assigned = Mailbox::query()->create(['company_id' => $company->id, 'email' => 'sales@alramzinternational.com', 'display_name' => 'Sales']);
        $hidden = Mailbox::query()->create(['company_id' => $company->id, 'email' => 'finance@alramzinternational.com', 'display_name' => 'Finance']);
        $user->mailboxes()->attach($assigned, ['can_send' => true]);
        MailMessage::query()->create(['mailbox_id' => $assigned->id, 'external_id' => 'one', 'from_address' => 'client@example.com', 'to_addresses' => [$assigned->email], 'subject' => 'Visible enquiry']);
        MailMessage::query()->create(['mailbox_id' => $hidden->id, 'external_id' => 'two', 'from_address' => 'private@example.com', 'to_addresses' => [$hidden->email], 'subject' => 'Hidden finance mail']);

        $this->actingAs($user)->get(route('mail.index'))->assertOk()->assertSee('Visible enquiry')->assertDontSee('Hidden finance mail');
    }
}
