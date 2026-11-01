<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_switch_login_to_arabic_and_rtl(): void
    {
        $this->from(route('login'))->get(route('locale.update', 'ar'))->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('مرحباً بعودتك');
    }

    public function test_unsupported_locale_is_rejected(): void
    {
        $this->get('/locale/fr')->assertNotFound();
    }

    public function test_authenticated_dashboard_uses_arabic_navigation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['locale' => 'ar'])->get(route('dashboard'))
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('نظرة عامة')
            ->assertSee('مركز البريد');
    }
}
