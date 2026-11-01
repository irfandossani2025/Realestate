<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_user_can_create_bilingual_owner_contact(): void
    {
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'property_manager', 'is_active' => true]);

        $this->actingAs($user)->post(route('contacts.store'), [
            'company_id' => $company->id, 'type' => 'owner', 'name' => 'Property Owner',
            'name_ar' => 'مالك العقار', 'phone' => '+968 9000 0000', 'preferred_language' => 'ar',
        ])->assertRedirectToRoute('contacts.index');

        $this->assertDatabaseHas('contacts', ['company_id' => $company->id, 'type' => 'owner', 'name_ar' => 'مالك العقار']);
    }
}
