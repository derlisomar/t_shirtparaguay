<?php

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
        $middleware->alias(['admin' => \App\Http\Middleware\EnsureAdmin::class]);
        $middleware->validateCsrfTokens(except: ['pagos/*/webhook']);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // «419 Page expired»: el formulario quedó abierto mucho tiempo o la sesión se cerró.
        // En vez de la pantalla de error, vuelve al formulario con un aviso para intentar de nuevo.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() !== 419) {
                return null;
            }
            if ($request->expectsJson()) {
                return response()->json(['message' => 'La página se venció. Recargala y probá de nuevo.'], 419);
            }

            return redirect()->back()
                ->withInput($request->except(['_token', 'password', 'password_confirmation', 'current_password', 'receipt', 'photos']))
                ->withErrors(['expired' => 'La página estuvo abierta mucho tiempo y se venció por seguridad. Probá de nuevo.']);
        });
    })->create();
