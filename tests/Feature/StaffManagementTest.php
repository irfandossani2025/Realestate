<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_employee_for_managed_company(): void
    {
        $company = Company::query()->create(['name' => 'Meyar', 'slug' => 'meyar', 'domain' => 'meyarforvaluation.com', 'business_type' => 'valuation']);
        $manager = User::factory()->create();
        $manager->companies()->attach($company, ['role' => 'manager', 'is_active' => true]);

        $this->actingAs($manager)->post(route('staff.store'), [
            'name' => 'Evaluator', 'email' => 'evaluator@meyarforvaluation.com',
            'password' => 'secure-password-123', 'company_id' => $company->id, 'role' => 'evaluator',
        ])->assertRedirectToRoute('staff.index');

        $employee = User::query()->where('email', 'evaluator@meyarforvaluation.com')->firstOrFail();
        $this->assertSame('evaluator', $employee->companies()->firstOrFail()->pivot->role);
    }

    public function test_regular_staff_cannot_manage_employees(): void
    {
        $company = Company::query()->create(['name' => 'Meyar', 'slug' => 'meyar', 'domain' => 'meyarforvaluation.com', 'business_type' => 'valuation']);
        $staff = User::factory()->create();
        $staff->companies()->attach($company, ['role' => 'evaluator', 'is_active' => true]);

        $this->actingAs($staff)->get(route('staff.index'))->assertForbidden();
    }
}
