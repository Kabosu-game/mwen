<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'created_by', 'zone_id',
        'title', 'description', 'type', 'status',
        'slots', 'slots_taken', 'payment',
        'address', 'latitude', 'longitude',
        'starts_at', 'ends_at',
        'requirements', 'equipment_provided',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'requirements' => 'array',
            'equipment_provided' => 'array',
            'payment' => 'float',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->reference = 'MIS-' . strtoupper(uniqid());
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function applications()
    {
        return $this->hasMany(MissionApplication::class);
    }

    public function acceptedApplicants()
    {
        return $this->hasMany(MissionApplication::class)->where('status', 'accepted');
    }

    public function getSlotsAvailableAttribute(): int
    {
        return max(0, $this->slots - $this->slots_taken);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && $this->slots_available > 0;
    }
}
