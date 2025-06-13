<?php

namespace App\Http\Controllers\Auth; // Sesuaikan namespace jika berbeda

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException; // Untuk error validasi

class LoginController extends Controller
{
    /**
     * Menampilkan form login.
     */
    public function showLoginForm()
    {
        // Buat view ini di resources/views/auth/login.blade.php
        return view('auth.login');
    }

    /**
     * Menangani upaya login.
     */
    public function login(Request $request)
    {
        // Validasi input
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Coba untuk melakukan autentikasi pengguna
        if (Auth::attempt($credentials, $request->boolean('remember'))) { // $request->boolean('remember') untuk fitur "Ingat Saya"
            $request->session()->regenerate(); // Regenerasi session untuk keamanan

            $user = Auth::user();

            // Jika user adalah admin, arahkan ke admin dashboard
            if ($user->is_admin) { // Pastikan model User Anda punya atribut 'is_admin'
                return redirect()->intended(route('admin.dashboard'));
            }

            // Jika bukan admin, arahkan ke halaman utama pengguna (misalnya '/')
            return redirect()->intended(route('home')); // Asumsi rute halaman utama pengguna bernama 'home'
        }

        // Jika autentikasi gagal
        throw ValidationException::withMessages([
            'email' => __('auth.failed'), // Menggunakan pesan default dari file lang/en/auth.php
        ]);
        // Atau bisa juga:
        // return back()->withErrors([
        //     'email' => 'Email atau password yang Anda masukkan salah.',
        // ])->onlyInput('email');
    }

    /**
     * Melakukan logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login'); // Arahkan ke halaman utama setelah logout
    }
}
