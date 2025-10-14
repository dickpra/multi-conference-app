<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConferenceSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'conference_id',
        'title',
        'date',
        'description',
    ];

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }
}