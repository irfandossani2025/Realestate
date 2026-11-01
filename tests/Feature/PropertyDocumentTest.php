<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PropertyDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_user_can_upload_private_property_document(): void
    {
        Storage::fake('local');
        $company = Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);
        $user = User::factory()->create();
        $user->companies()->attach($company, ['role' => 'property_manager', 'is_active' => true]);
        $property = Property::query()->create(['company_id' => $company->id, 'reference' => 'RE-400', 'property_type' => 'villa', 'purpose' => 'sale', 'title' => 'Documented Villa']);

        $this->actingAs($user)->post(route('properties.documents.store', $property), [
            'document' => UploadedFile::fake()->create('title.pdf', 100, 'application/pdf'),
            'category' => 'property_title', 'language' => 'en',
        ])->assertRedirectToRoute('properties.index');

        $document = $property->documents()->firstOrFail();
        Storage::disk('local')->assertExists($document->path);
        $this->assertFalse($document->is_public);
    }
}
