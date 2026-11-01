<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_critical_model_changes_capture_actor_and_values(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $this->actingAs($user);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-600', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Audited Villa']);
        $property->update(['publication_status' => 'review']);

        $this->assertDatabaseHas('audit_logs', ['company_id' => $company->id, 'user_id' => $user->id, 'auditable_id' => $property->id, 'event' => 'created']);
        $this->assertDatabaseHas('audit_logs', ['company_id' => $company->id, 'user_id' => $user->id, 'auditable_id' => $property->id, 'event' => 'updated']);
    }
}
