<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conference extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role') // Penting! Agar bisa mengakses kolom 'role'
            ->withTimestamps();
    }

    public function schedules()
    {
        return $this->hasMany(ConferenceSchedule::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    // app/Models/Conference.php
    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}
