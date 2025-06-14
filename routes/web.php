<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrammarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImageProcessingController;
use App\Http\Controllers\SummarizerController;
use App\Http\Controllers\ImageProcessController;
use App\Http\Controllers\DocumentConversionController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Rute untuk halaman-halaman utama (misalnya dashboard pengguna)
Route::get('/', [DashboardController::class, 'index'])->name('home'); // Ditambahkan name('home')
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.user'); // Diberi nama berbeda untuk dashboard pengguna

// Rute untuk menampilkan halaman Grammar Checker (view)
Route::get('/grammar', [DashboardController::class, 'grammar'])->name('grammar.view');

// Rute POST untuk mengirim teks ke Grammar Checker API (dari frontend)
Route::post('/grammar-check', [GrammarController::class, 'check'])->name('grammar.check');

// Rute POST untuk mengunduh teks yang sudah diperbaiki sebagai PDF
Route::post('/download-grammar-pdf', [GrammarController::class, 'downloadPdf'])->name('grammar.download');

// Rute untuk menampilkan halaman upscale (view)
Route::get('/upscaling', [DashboardController::class, 'upscaleimage'])->name('upscaling.view');

// Rute POST untuk Upscaling Image
Route::post('/upscale-image', [ImageProcessingController::class, 'upscale'])->name('image.upscale');

// Untuk menampilkan halaman Text Summarizer
Route::get('/text-summarizer', function () {
    return view('text-summarizer');
})->name('text-summarizer.view');

// Untuk memproses permintaan peringkasan teks
Route::post('/summarize-text', [SummarizerController::class, 'summarize'])->name('text.summarize');

// Untuk menampilkan halaman remove background
Route::get('/remove-background', function () {
    return view('removebg');
})->name('removebg.view');

// Untuk memproses permintaan remove background
Route::post('/remove-background-process', [ImageProcessController::class, 'removeBackground'])->name('removebg.process');

// Untuk menampilkan halaman Word to PDF
Route::get('/wordtopdf', function () {
    return view('wordtopdf');
})->name('wordtopdf.view');

// Endpoint untuk MEMPROSES konversi Word ke PDF
Route::post('/convert-word-to-pdf-process', [DocumentConversionController::class, 'convertToPdf'])->name('wordtopdf.process');


// Rute-rute Autentikasi dari Laravel Breeze
// Ini akan meng-include rute seperti /login, /register, /logout, dll.
// Pastikan file ini ada jika Anda telah menjalankan breeze:install
if (file_exists(base_path('routes/auth.php'))) {
    require __DIR__ . '/auth.php';
}

// Rute untuk menampilkan form login
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login'); // <-- PENTING: ->name('login')

// Rute untuk memproses data login yang dikirim dari form
Route::post('/login', [LoginController::class, 'login']);

// Rute untuk logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');


// Group untuk route admin
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Routes untuk manajemen user
        // Ganti dari UserController ke AdminUserController
        Route::resource('users', App\Http\Controllers\Admin\AdminUserController::class);
    });
