<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyPublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_publish_property_to_public_inventory_feed(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $manager = User::factory()->create();
        $manager->companies()->attach($company, ['role' => 'manager', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-100', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Qurum Villa', 'title_ar' => 'فيلا القرم']);

        $this->actingAs($manager)->patch(route('properties.publication.update', $property), ['publication_status' => 'published'])->assertRedirectToRoute('properties.index');

        $this->getJson(route('api.properties.index'))
            ->assertOk()
            ->assertJsonPath('data.0.reference', 'RE-100')
            ->assertJsonMissingPath('data.0.owner_id');
    }

    public function test_regular_staff_cannot_publish_property(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $staff = User::factory()->create();
        $staff->companies()->attach($company, ['role' => 'sales_agent', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-101', 'property_type' => 'apartment', 'purpose' => 'rent', 'title' => 'Muscat Apartment']);

        $this->actingAs($staff)->patch(route('properties.publication.update', $property), ['publication_status' => 'published'])->assertForbidden();
        $this->assertSame('internal', $property->fresh()->publication_status);
    }
}
