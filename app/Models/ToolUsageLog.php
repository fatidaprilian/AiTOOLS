<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolUsageLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tool_usage_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tool_name',
        'user_id',
        'status',
        'details',
        'processing_time_ms',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'details' => 'array', // Otomatis cast kolom details ke array/objek saat diambil & disimpan sebagai JSON
        'processing_time_ms' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the log. (Opsional)
     */
    public function user()
    {
        // Pastikan Anda memiliki model User jika relasi ini diaktifkan
        // return $this->belongsTo(User::class); 
        return $this->belongsTo('App\Models\User', 'user_id'); // Contoh jika model User ada
    }
}
