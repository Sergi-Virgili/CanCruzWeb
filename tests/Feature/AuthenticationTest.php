<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_log_in(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-pass')]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secret-pass',
        ])->assertRedirect(route('admin.reservations.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-pass')]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_regenerates_session(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-pass')]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secret-pass',
        ])->assertRedirect(route('admin.reservations.index'));

        $this->assertTrue(session()->has('_token'));
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create(['password' => Hash::make('secret-pass')]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secret-pass',
        ])->assertRedirect(route('admin.reservations.index'));

        $this->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_registration_and_password_reset_routes_are_absent(): void
    {
        $routes = Route::getRoutes()->getRoutesByName();

        $this->assertArrayNotHasKey('register', $routes);
        $this->assertArrayNotHasKey('password.request', $routes);
        $this->assertArrayNotHasKey('password.email', $routes);
        $this->assertArrayNotHasKey('password.reset', $routes);
        $this->assertArrayNotHasKey('password.update', $routes);
    }

    public function test_login_view_is_accessible(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_login_view_presents_the_branded_admin_experience(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Área privada')
            ->assertSee('Volver a la web')
            ->assertSee('Recordarme')
            ->assertSee('class="login-page"', false)
            ->assertSee('name="remember"', false);
    }
}
