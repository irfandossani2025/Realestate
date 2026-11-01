<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lease;
use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_rental_transaction_becomes_tracked_lease_and_enters_renewal_period(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'leasing_agent', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-700', 'property_type' => 'apartment', 'purpose' => 'rent', 'title' => 'Lease Apartment']);
        $transaction = PropertyTransaction::query()->create(['company_id' => $company->id, 'property_id' => $property->id, 'reference' => 'TRX-700', 'type' => 'rental', 'status' => 'active', 'agreed_value' => 6000]);

        $this->actingAs($user)->post(route('leases.store'), [
            'property_transaction_id' => $transaction->id, 'starts_on' => today()->subYear()->toDateString(),
            'ends_on' => today()->addDays(30)->toDateString(), 'rent_amount' => 500,
            'rent_frequency' => 'monthly', 'security_deposit' => 500, 'renewal_notice_days' => 60,
        ])->assertRedirectToRoute('leases.index');
        $this->artisan('leases:check-renewals')->assertSuccessful();

        $this->assertSame('due_soon', Lease::query()->firstOrFail()->renewal_status);
    }
}
