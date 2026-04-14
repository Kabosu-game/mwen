<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'status',
        'avatar', 'address', 'commune', 'department',
        'latitude', 'longitude', 'national_id', 'birth_date', 'gender',
        'is_available', 'points', 'balance', 'fcm_token',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_available' => 'boolean',
            'birth_date' => 'date',
            'latitude' => 'float',
            'longitude' => 'float',
            'balance' => 'float',
        ];
    }

    // Scopes
    public function scopeCitizens($query)
    {
        return $query->where('role', 'citizen');
    }

    public function scopeCollectors($query)
    {
        return $query->where('role', 'collector');
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Relations
    public function collectionRequests()
    {
        return $this->hasMany(CollectionRequest::class, 'citizen_id');
    }

    public function assignedCollections()
    {
        return $this->hasMany(CollectionRequest::class, 'collector_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function assignedReports()
    {
        return $this->hasMany(Report::class, 'assigned_to');
    }

    public function missionApplications()
    {
        return $this->hasMany(MissionApplication::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class, 'created_by');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helpers
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isCollector(): bool
    {
        return $this->role === 'collector';
    }

    public function isCitizen(): bool
    {
        return $this->role === 'citizen';
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription()->exists();
    }

    // Filament admin panel
    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
}
