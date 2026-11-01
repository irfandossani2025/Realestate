<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Property;
use App\Models\User;
use App\Models\Viewing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ViewingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_schedules_and_completes_viewing(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $agent = User::factory()->create();
        $agent->companies()->attach($company, ['role' => 'sales_agent', 'is_active' => true]);
        $contact = Contact::query()->create(['company_id' => $company->id, 'name' => 'Buyer', 'type' => 'client']);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-1000', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Viewing Villa']);

        $this->actingAs($agent)->post(route('viewings.store'), ['property_id' => $property->id, 'contact_id' => $contact->id, 'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s')])->assertRedirectToRoute('viewings.index');
        $viewing = Viewing::query()->firstOrFail();
        $this->actingAs($agent)->patch(route('viewings.update', $viewing), ['status' => 'completed', 'outcome' => 'Client requested an offer.'])->assertRedirectToRoute('viewings.index');

        $this->assertDatabaseHas('viewings', ['id' => $viewing->id, 'status' => 'completed', 'assigned_to' => $agent->id]);
    }
}
