<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tool_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tool_name'); // Nama tool yang digunakan
            $table->unsignedBigInteger('user_id')->nullable(); // ID pengguna, nullable jika tidak ada login
            $table->string('status')->default('success'); // Status penggunaan (misalnya: success, error)
            $table->text('details')->nullable(); // Detail tambahan dalam format JSON
            $table->unsignedInteger('processing_time_ms')->nullable(); // Waktu proses dalam milidetik
            $table->timestamps(); // Kolom created_at (digunakan sebagai waktu penggunaan) dan updated_at

            // Jika Anda memiliki tabel 'users' dan ingin membuat foreign key (opsional jika user_id selalu null)
            // Pastikan tabel 'users' sudah ada sebelum menjalankan migrasi ini jika menggunakan foreign key.
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tool_usage_logs');
    }
};
