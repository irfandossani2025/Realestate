<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_user_can_open_maintenance_request_for_company_property(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'property_manager', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-300', 'property_type' => 'apartment', 'purpose' => 'rent', 'title' => 'Qurum Apartment']);

        $this->actingAs($user)->post(route('maintenance.store'), [
            'company_id' => $company->id, 'property_id' => $property->id,
            'title' => 'Air conditioner', 'description' => 'Cooling is not working.',
            'priority' => 'high', 'estimated_cost' => 45.500,
        ])->assertRedirectToRoute('maintenance.index');

        $this->assertDatabaseHas('maintenance_requests', ['property_id' => $property->id, 'priority' => 'high', 'status' => 'open']);
    }
}
