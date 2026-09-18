<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_an_administrator_from_a_secret(): void
    {
        config()->set('admin.name', 'Can Cruz Admin');
        config()->set('admin.bootstrap_password', 'a-secure-test-password');

        $this->artisan('admin:create', ['email' => 'admin@example.com'])
            ->assertSuccessful();

        $user = User::whereEmail('admin@example.com')->firstOrFail();
        $this->assertSame('Can Cruz Admin', $user->name);
        $this->assertTrue(Hash::check('a-secure-test-password', $user->password));
    }

    public function test_command_rejects_invalid_email(): void
    {
        config()->set('admin.name', 'Can Cruz Admin');
        config()->set('admin.bootstrap_password', 'a-secure-test-password');

        $this->artisan('admin:create', ['email' => 'not-an-email'])
            ->assertFailed()
            ->expectsOutputToContain('A valid administrator email is required.');

        $this->assertDatabaseMissing('users', ['email' => 'not-an-email']);
    }

    public function test_command_fails_when_no_password_in_non_interactive_mode(): void
    {
        config()->set('admin.name', 'Can Cruz Admin');
        config()->set('admin.bootstrap_password', null);

        $this->artisan('admin:create', ['email' => 'admin@example.com'])
            ->assertFailed()
            ->expectsOutputToContain('Set ADMIN_BOOTSTRAP_PASSWORD for non-interactive use.');

        $this->assertDatabaseMissing('users', ['email' => 'admin@example.com']);
    }

    public function test_command_updates_existing_administrator(): void
    {
        config()->set('admin.name', 'Can Cruz Admin');
        config()->set('admin.bootstrap_password', 'original-password');

        User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Old Name',
            'password' => Hash::make('old-password'),
        ]);

        $this->artisan('admin:create', ['email' => 'admin@example.com'])
            ->assertSuccessful();

        $user = User::whereEmail('admin@example.com')->firstOrFail();
        $this->assertSame('Can Cruz Admin', $user->name);
        $this->assertTrue(Hash::check('original-password', $user->password));
    }
}
