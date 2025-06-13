<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (VERSI DEBUGGING)
|--------------------------------------------------------------------------
|
| Semua rute asli dinonaktifkan sementara untuk mencari sumber masalah.
|
*/

// Rute tes sederhana. Jika ini berhasil, masalahnya ada pada salah satu
// controller atau rute yang dinonaktifkan di bawah.
Route::get('/', function () {
    return '<h1>Aplikasi Berhasil Berjalan!</h1><p>Masalah ada pada salah satu rute/controller asli.</p>';
});


/*
// ======================================================================
// SEMUA RUTE ASLI ANDA DINONAKTIFKAN SEMENTARA DI BAWAH INI
// ======================================================================

// Rute untuk halaman-halaman utama (misalnya dashboard pengguna)
// Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
// Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.user');

// Rute untuk menampilkan halaman Grammar Checker (view)
// Route::get('/grammar', [App\Http\Controllers\DashboardController::class, 'grammar'])->name('grammar.view');

// Rute POST untuk mengirim teks ke Grammar Checker API (dari frontend)
// Route::post('/grammar-check', [App\Http\Controllers\GrammarController::class, 'check'])->name('grammar.check');

// Rute POST untuk mengunduh teks yang sudah diperbaiki sebagai PDF
// Route::post('/download-grammar-pdf', [App\Http\Controllers\GrammarController::class, 'downloadPdf'])->name('grammar.download');

// Rute untuk menampilkan halaman upscale (view)
// Route::get('/upscaling', [App\Http\Controllers\DashboardController::class, 'upscaleimage'])->name('upscaling.view');

// Rute POST untuk Upscaling Image
// Route::post('/upscale-image', [App\Http\Controllers\ImageProcessingController::class, 'upscale'])->name('image.upscale');

// Untuk menampilkan halaman Text Summarizer
// Route::get('/text-summarizer', function () {
//     return view('text-summarizer');
// })->name('text-summarizer.view');

// Untuk memproses permintaan peringkasan teks
// Route::post('/summarize-text', [App\Http\Controllers\SummarizerController::class, 'summarize'])->name('text.summarize');

// Untuk menampilkan halaman remove background
// Route::get('/remove-background', function () {
//     return view('removebg');
// })->name('removebg.view');

// Untuk memproses permintaan remove background
// Route::post('/remove-background-process', [App\Http\Controllers\ImageProcessController::class, 'removeBackground'])->name('removebg.process');

// Untuk menampilkan halaman Word to PDF
// Route::get('/wordtopdf', function () {
//     return view('wordtopdf');
// })->name('wordtopdf.view');

// Endpoint untuk MEMPROSES konversi Word ke PDF
// Route::post('/convert-word-to-pdf-process', [App\Http\Controllers\DocumentConversionController::class, 'convertToPdf'])->name('wordtopdf.process');

// Rute-rute Autentikasi dari Laravel Breeze
// if (file_exists(base_path('routes/auth.php'))) {
//     require __DIR__ . '/auth.php';
// }

// Rute untuk menampilkan form login
// Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');

// Rute untuk memproses data login yang dikirim dari form
// Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);

// Rute untuk logout
// Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

// Group untuk route admin
// Route::middleware(['auth', 'admin'])
//     ->prefix('admin')
//     ->name('admin.')
//     ->group(function () {
//         Route::get('/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])
//             ->name('dashboard');
//     });

*/