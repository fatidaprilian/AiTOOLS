<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; // Pastikan ini di-import

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Daftarkan alias middleware Anda di sini
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdminMiddleware::class, // <-- INI YANG DITAMBAHKAN

            // Catatan: Alias bawaan Laravel seperti 'auth' dan 'guest'
            // biasanya sudah di-handle secara internal di Laravel 11+.
            // Anda hanya perlu menambahkan alias kustom Anda seperti 'admin'.
            // Jika Anda perlu meng-override alias bawaan, Anda bisa menambahkannya di sini juga, contoh:
            // 'auth' => \App\Http\Middleware\Authenticate::class,
            // 'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        ]);

        // Jika Anda perlu memodifikasi grup middleware 'web' atau 'api' (jarang diperlukan untuk kasus sederhana):
        /*
        $middleware->web(append: [
            // \App\Http\Middleware\CustomWebMiddleware::class,
        ]);

        $middleware->api(prepend: [
            // \App\Http\Middleware\CustomApiMiddleware::class,
        ]);
        */
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Konfigurasi penanganan exception bisa ditambahkan di sini jika perlu
        // Contoh:
        // $exceptions->render(function (Throwable $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json(['message' => $e->getMessage()], 500);
        //     }
        // });
    })->create();
