<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/confirm-password');

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/confirm-password', [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_password_confirmation_is_limited_to_six_attempts_per_minute()
    {
        $user = User::factory()->create();

        foreach (range(1, 6) as $i) {
            $this->actingAs($user)->post('/confirm-password', ['password' => 'mauvais-mot-de-passe']);
        }

        $this->actingAs($user)->post('/confirm-password', ['password' => 'mauvais-mot-de-passe'])->assertStatus(429);
    }
}
