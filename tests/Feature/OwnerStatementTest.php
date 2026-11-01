<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Company;
use App\Models\Contact;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerStatementTest extends TestCase
{
    use RefreshDatabase;

    public function test_statement_reconciles_owner_income_and_deductions(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'manager', 'is_active' => true]);
        $owner = Contact::query()->create(['company_id' => $company->id, 'name' => 'Property Owner', 'type' => 'owner']);
        $property = Property::query()->create(['company_id' => $company->id, 'owner_id' => $owner->id, 'reference' => 'RE-900', 'property_type' => 'villa', 'purpose' => 'rent', 'title' => 'Owner Villa']);
        $transaction = PropertyTransaction::query()->create(['company_id' => $company->id, 'property_id' => $property->id, 'reference' => 'TRX-900', 'type' => 'rental', 'status' => 'active', 'agreed_value' => 10000]);
        Payment::query()->create(['company_id' => $company->id, 'property_transaction_id' => $transaction->id, 'reference' => 'PAY-900', 'amount' => 10000, 'method' => 'cash', 'status' => 'approved']);
        MaintenanceRequest::query()->create(['company_id' => $company->id, 'property_id' => $property->id, 'reference' => 'MNT-900', 'title' => 'Repair', 'description' => 'Repair', 'actual_cost' => 200]);
        Commission::query()->create(['company_id' => $company->id, 'property_transaction_id' => $transaction->id, 'user_id' => $user->id, 'calculation_type' => 'fixed', 'rate' => 1000, 'basis_amount' => 10000, 'amount' => 1000, 'status' => 'approved']);

        $this->actingAs($user)->get(route('owners.statements'))->assertOk()->assertSee('Property Owner')->assertSee('OMR 8,800.000');
    }
}
