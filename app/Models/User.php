<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserStatus; // <-- Import enum
use Filament\Models\Contracts\HasTenants; // <-- Import interface
use Filament\Panel;
use Illuminate\Database\Eloquent\Model; // <-- Import Model
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // <-- Import BelongsToMany
use Illuminate\Support\Collection; // <-- Import Collection


class User extends Authenticatable implements HasTenants
{
    use HasApiTokens, HasFactory, Notifiable;


    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    //     'is_super_admin', // <-- (FIX #1) TAMBAHKAN INI
    // ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean', // <-- (FIX #2) TAMBAHKAN INI
            'status' => UserStatus::class,
        ];
    }

    // --- MULAI METHOD UNTUK TENANCY ---

    public function getTenants(Panel $panel): Collection
    {
        // Mengembalikan semua conference di mana user ini memiliki peran
        return $this->conferences;
    }

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function canAccessTenant(Model $tenant): bool
    {
        // Memeriksa apakah user ini terhubung dengan conference (tenant) yang diberikan
        return $this->conferences->contains($tenant);
    }
    // --- SELESAI METHOD UNTUK TENANCY ---



    public function submissionsToReview()
    {
        return $this->belongsToMany(Submission::class, 'submission_user', 'user_id', 'submission_id')
            ->withPivot(['recommendation', 'comments', 'status']) // <-- Tambahkan ini
            ->withTimestamps();
    }

    // app/Models/User.php
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }


}
