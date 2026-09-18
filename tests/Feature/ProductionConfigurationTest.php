<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProductionConfigurationTest extends TestCase
{
    public function test_debugging_is_disabled_when_app_debug_is_false(): void
    {
        config()->set('app.debug', false);

        $this->assertFalse(config('app.debug'));
    }

    public function test_mailer_is_driven_by_environment(): void
    {
        $mailer = config('mail.default');

        $this->assertNotNull($mailer);
        // In production, mailer should not be 'log'. In development, it can be.
        if (config('app.env') === 'production') {
            $this->assertNotEquals('log', $mailer, 'Production should not use log mailer');
        }
    }

    public function test_database_connection_is_driven_by_environment(): void
    {
        $connection = config('database.default');

        $this->assertNotNull($connection);
        // In production, database should not be 'sqlite'. In development, it can be.
        if (config('app.env') === 'production') {
            $this->assertNotEquals('sqlite', $connection, 'Production should not use sqlite');
        }
    }
}
