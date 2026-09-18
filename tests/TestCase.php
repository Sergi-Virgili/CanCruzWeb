<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Render views without compiled assets so tests do not depend on a Vite build.
        $this->withoutVite();

        // Disable CSRF middleware for most tests
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }
}
