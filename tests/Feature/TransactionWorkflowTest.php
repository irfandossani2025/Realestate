<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_al_ramz_user_can_create_a_transaction_and_record_a_payment(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz Real Estate', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'sales_agent', 'is_active' => true]);
        $client = Contact::query()->create(['company_id' => $company->id, 'name' => 'Client', 'type' => 'client']);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-001', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Muscat Villa']);

        $this->actingAs($user)->post(route('transactions.store'), [
            'company_id' => $company->id, 'property_id' => $property->id, 'client_id' => $client->id,
            'type' => 'sale', 'agreed_value' => 125000, 'deposit_amount' => 5000,
        ])->assertRedirectToRoute('transactions.index');

        $transaction = PropertyTransaction::query()->firstOrFail();
        $this->actingAs($user)->post(route('transactions.payments.store', $transaction), [
            'amount' => 5000, 'method' => 'bank_transfer', 'received_on' => now()->toDateString(),
        ])->assertRedirectToRoute('transactions.index');

        $this->assertDatabaseHas('payments', ['property_transaction_id' => $transaction->id, 'amount' => 5000, 'status' => 'pending']);
    }
}
