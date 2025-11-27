<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Attendee extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relasi ke User (Peserta)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Conference
    public function conference(): BelongsTo
    {
        return $this->belongsTo(Conference::class);
    }
}
