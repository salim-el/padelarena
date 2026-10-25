<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_a_password_shorter_than_15_characters_is_refused()
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'court@example.test',
            'password' => 'quatorze-car14',
            'password_confirmation' => 'quatorze-car14',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'mot-de-passe-long-2026',
            'password_confirmation' => 'mot-de-passe-long-2026',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
