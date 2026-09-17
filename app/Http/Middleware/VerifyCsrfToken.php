<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        '*',
    ];

    protected function inTestingEnvironment(): bool
    {
        return app()->environment('testing');
    }

    public function handle($request, \Closure $next)
    {
        if ($this->inTestingEnvironment()) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
