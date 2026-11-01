<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_user_can_create_vat_invoice(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals', 'default_vat_rate' => 5]);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'finance', 'is_active' => true]);

        $this->actingAs($user)->post(route('invoices.store'), [
            'company_id' => $company->id, 'issue_date' => now()->toDateString(),
            'description' => 'Agency service', 'description_ar' => 'خدمة الوساطة',
            'quantity' => 1, 'unit_price' => 100,
        ])->assertRedirectToRoute('invoices.index');

        $this->assertDatabaseHas('invoices', ['company_id' => $company->id, 'subtotal' => 100, 'vat_amount' => 5, 'total' => 105, 'status' => 'draft']);
        $this->assertDatabaseHas('invoice_lines', ['description' => 'Agency service', 'vat_rate' => 5]);
    }

    public function test_sales_agent_cannot_create_invoice(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'sales_agent', 'is_active' => true]);

        $this->actingAs($user)->post(route('invoices.store'), [
            'company_id' => $company->id, 'issue_date' => now()->toDateString(),
            'description' => 'Agency service', 'quantity' => 1, 'unit_price' => 100,
        ])->assertForbidden();
    }
}
