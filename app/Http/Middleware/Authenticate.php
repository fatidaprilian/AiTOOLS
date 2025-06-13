<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request; // Penting untuk type hinting

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo(Request $request): ?string // Pastikan return type ?string
    {
        // Jika request adalah AJAX (mengharapkan JSON), kembalikan null agar Laravel mengirim 401.
        // Jika tidak, arahkan ke rute yang bernama 'login'.
        return $request->expectsJson() ? null : route('login');
    }
}
