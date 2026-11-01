<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommissionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_calculates_percentage_commission(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $manager = User::factory()->create();
        $agent = User::factory()->create();
        $manager->companies()->attach($company, ['role' => 'manager', 'is_active' => true]);
        $agent->companies()->attach($company, ['role' => 'sales_agent', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-800', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Commission Villa']);
        $transaction = PropertyTransaction::query()->create(['company_id' => $company->id, 'property_id' => $property->id, 'reference' => 'TRX-800', 'type' => 'sale', 'status' => 'active', 'agreed_value' => 100000]);

        $this->actingAs($manager)->post(route('commissions.store'), ['company_id' => $company->id, 'property_transaction_id' => $transaction->id, 'user_id' => $agent->id, 'calculation_type' => 'percentage', 'rate' => 2.5])->assertRedirectToRoute('commissions.index');

        $this->assertDatabaseHas('commissions', ['amount' => 2500, 'status' => 'pending']);
    }
}
