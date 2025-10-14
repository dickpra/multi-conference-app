<?php

namespace App\Models;

use App\Enums\SubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    // protected $fillable = [
    //     'conference_id',
    //     'user_id',
    //     'title',
    //     'abstract',
    //     'keywords',
    //     'full_paper_path',
    //     'revised_paper_path',
    //     'status',
    // ];
    protected $guarded = [];

    protected $casts = [
        'status' => SubmissionStatus::class,
    ];
    

    public function conference()
    {
        return $this->belongsTo(Conference::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // app/Models/Submission.php
    public function reviews()
    {
        return $this->hasMany(Review::class)->orderBy('created_at', 'desc');
    }
    // Juga tambahkan relasi untuk penugasan
    public function assignedReviewers()
    {
        return $this->belongsToMany(User::class, 'submission_user', 'submission_id', 'user_id')
        ->withPivot(['status'])
        ->withTimestamps()
        ;
    }
    

    
    
}