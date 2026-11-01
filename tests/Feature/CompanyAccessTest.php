<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_employee_can_have_a_distinct_role_at_more_than_one_company(): void
    {
        $meyar = Company::query()->create([
            'name' => 'Meyar for Valuation',
            'slug' => 'meyar',
            'domain' => 'meyarforvaluation.com',
            'business_type' => 'valuation',
        ]);
        $marsous = Company::query()->create([
            'name' => 'Marsous Marketing',
            'slug' => 'marsous',
            'domain' => 'marsousrealestate.com',
            'business_type' => 'marketing',
        ]);
        $employee = User::factory()->create();

        $employee->companies()->attach($meyar, ['role' => 'evaluator', 'is_active' => true]);
        $employee->companies()->attach($marsous, ['role' => 'marketing_manager', 'is_active' => true]);

        $this->assertSame([
            'marsous' => 'marketing_manager',
            'meyar' => 'evaluator',
        ], $employee->companies()->pluck('company_user.role', 'slug')->sortKeys()->all());
    }
}
