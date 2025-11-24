<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardSetting extends Model
{
    protected $guarded = [];

    protected $casts = [
        'about_me' => 'array',
        'credit' => 'array',
        'guidebook' => 'array',
        'metodologi' => 'array',
    ];
}