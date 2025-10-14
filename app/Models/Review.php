<?php

namespace App\Models;

use App\Enums\Recommendation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['submission_id', 'user_id', 'recommendation', 'comments'];
    protected $casts = ['recommendation' => Recommendation::class];

    public function submission() { return $this->belongsTo(Submission::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'user_id'); }
    
}