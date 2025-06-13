<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->is_admin) { // 'is_admin' sesuai nama kolom/atribut di model User
            return $next($request);
        }
        if (!Auth::check()) { // Jika belum login sama sekali (meskipun auth middleware utama harusnya handle ini)
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }
        abort(403, 'AKSES DITOLAK. Anda tidak memiliki izin untuk mengakses halaman ini sebagai admin.');
    }
}
