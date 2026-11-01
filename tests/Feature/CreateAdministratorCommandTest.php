<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdministratorCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_administrator_for_every_company(): void
    {
        Company::query()->create(['name' => 'Meyar', 'slug' => 'meyar', 'domain' => 'meyarforvaluation.com', 'business_type' => 'valuation']);
        Company::query()->create(['name' => 'Al Ramz', 'slug' => 'al-ramz', 'domain' => 'alramzinternational.com', 'business_type' => 'sales_rentals']);

        $this->artisan('erp:create-admin', ['email' => 'admin@alramzinternational.com', '--name' => 'ERP Admin'])
            ->expectsQuestion('Password (minimum 12 characters)', 'a-secure-password')
            ->assertSuccessful();

        $user = User::query()->where('email', 'admin@alramzinternational.com')->firstOrFail();
        $this->assertCount(2, $user->companies);
        $this->assertSame(['admin'], $user->companies->pluck('pivot.role')->unique()->values()->all());
    }
}
