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
use App\Models\Conference; // <-- Import Conference model
use App\Enums\ConferenceRole; // <-- Pastikan ini di-import
use App\Models\Submission; // <-- Import Submission model
use App\Models\Review; // <-- Import Review model
use Filament\Facades\Filament;

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
        // 1. Logika Khusus Panel CHAIR
        // Switcher hanya boleh menampilkan konferensi di mana user adalah CHAIR
        if ($panel->getId() === 'chair') {
            return $this->conferences()
                ->wherePivot('role', ConferenceRole::Chair->value) // Pastikan pakai ->value jika Enum
                ->get();
        }

        // 2. Logika Khusus Panel REVIEWER
        // Switcher hanya boleh menampilkan konferensi di mana user adalah REVIEWER
        if ($panel->getId() === 'reviewer') {
            return $this->conferences()
                ->wherePivot('role', ConferenceRole::Reviewer->value)
                ->get();
        }

        // 3. Logika Khusus Panel ADMIN (Opsional)
        // Jika Super Admin ingin melihat switcher di panel admin (biasanya tidak perlu, tapi untuk jaga-jaga)
        if ($panel->getId() === 'admin' && $this->is_super_admin) {
            return \App\Models\Conference::all();
        }

        // Default: Kembalikan koleksi kosong agar tidak ada kebocoran data di panel lain
        return collect();
    }

    // app/Models/User.php

    public function canAccessTenant(Model $tenant): bool
    {
        // Super Admin Pass
        if ($this->is_super_admin && Filament::getCurrentPanel()->getId() === 'admin') {
            return true;
        }

        $panelId = Filament::getCurrentPanel()->getId();

        if ($panelId === 'chair') {
            return $this->conferences()
                ->where('conference_id', $tenant->id)
                ->wherePivot('role', ConferenceRole::Chair->value)
                ->exists();
        }

        if ($panelId === 'reviewer') {
            return $this->conferences()
                ->where('conference_id', $tenant->id)
                ->wherePivot('role', ConferenceRole::Reviewer->value)
                ->exists();
        }

        // Default block access jika tidak sesuai kriteria di atas
        return false;
    }

    // public function getTenants(Panel $panel): Collection
    // {
    //     // Mengembalikan semua conference di mana user ini memiliki peran
    //     return $this->conferences;
    // }

    public function conferences(): BelongsToMany
    {
        return $this->belongsToMany(Conference::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    // public function canAccessTenant(Model $tenant): bool
    // {
    //     // Memeriksa apakah user ini terhubung dengan conference (tenant) yang diberikan
    //     return $this->conferences->contains($tenant);
    // }
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
