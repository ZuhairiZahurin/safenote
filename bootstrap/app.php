<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\RequirePasswordChange;
use App\Http\Middleware\SessionTimeout;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);

        $middleware->appendToGroup('web', SessionTimeout::class);
        $middleware->appendToGroup('web', RequirePasswordChange::class);

        // Behind a hosting platform's TLS proxy the app only sees plain HTTP.
        // Trusting the forwarded headers keeps generated links on https and lets
        // the session cookie be marked secure.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
