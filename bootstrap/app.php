<?php

use App\Http\Middleware\IsAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            if (config('rba.enabled', true)) {
                require __DIR__.'/../routes/rba.php';
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Untuk request AJAX/JSON, kembalikan JSON yang jelas ketika CSRF
        // tidak valid (419) — misalnya token basi karena tab ganda atau
        // double-submit setelah session regenerate. Tanpa ini user hanya
        // melihat halaman error HTML generik "CSRF token mismatch".
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() === 419 && ($request->expectsJson() || $request->ajax())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi keamanan kedaluwarsa. Memuat ulang halaman...',
                    'reload' => true,
                ], 419);
            }

            return null;
        });
    })->create();
