<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValuationLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluator_can_link_property_and_complete_valuation(): void
    {
        $meyar = Company::query()->create(['name' => 'Meyar', 'slug' => 'meyar', 'domain' => 'meyarforvaluation.com', 'business_type' => 'valuation']);
        $alRamz = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($meyar, ['role' => 'evaluator', 'is_active' => true]);
        $user->companies()->attach($alRamz, ['role' => 'team_member', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $alRamz->id, 'reference' => 'RE-500', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Valued Villa']);

        $this->actingAs($user)->post(route('valuations.store'), ['company_id' => $meyar->id, 'property_id' => $property->id, 'purpose' => 'sale'])->assertRedirectToRoute('valuations.index');
        $valuation = ValuationRequest::query()->firstOrFail();
        $this->actingAs($user)->patch(route('valuations.status.update', $valuation), ['status' => 'completed'])->assertRedirectToRoute('valuations.index');

        $this->assertNotNull($valuation->fresh()->completed_at);
    }
}
