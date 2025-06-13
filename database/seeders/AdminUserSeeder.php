<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin AlinAja',
            'email' => 'admin@admin.com', // Ganti dengan email Anda
            'password' => Hash::make('123'), // Ganti dengan password yang kuat
            'is_admin' => true,
            'email_verified_at' => now(), // Opsional, jika Anda menggunakan fitur verifikasi email
        ]);
    }
}
