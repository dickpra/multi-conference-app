<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = [
        'name',
        'code',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Event "booted" untuk menangani logika database otomatis.
     */
    protected static function booted(): void
    {
        // Saat bahasa baru dibuat atau diedit
        static::saving(function (Language $language) {
            // Jika bahasa ini diset sebagai default
            if ($language->is_default) {
                // Ubah semua bahasa LAIN menjadi non-default
                static::where('id', '!=', $language->id)->update(['is_default' => false]);
            }
        });
    }
}