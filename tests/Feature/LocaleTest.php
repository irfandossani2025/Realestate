<?php

namespace Tests\Feature;

use Tests\TestCase;

class LocaleTest extends TestCase
{
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
}
