<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_only_contains_assigned_company_data(): void
    {
        $assigned = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $hidden = Company::query()->create(['name' => 'Hidden Company', 'slug' => 'hidden', 'domain' => 'hidden.example', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($assigned, ['role' => 'manager', 'is_active' => true]);
        Property::query()->create(['company_id' => $assigned->id, 'reference' => 'RE-200', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Visible Villa']);
        Property::query()->create(['company_id' => $hidden->id, 'reference' => 'RE-201', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Hidden Villa']);

        $this->actingAs($user)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Al Ramz')
            ->assertDontSee('Hidden Company');
    }
}
