<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectionRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'citizen_id', 'collector_id', 'zone_id',
        'status', 'waste_type', 'priority',
        'address', 'latitude', 'longitude',
        'notes', 'photos', 'scheduled_at',
        'assigned_at', 'started_at', 'completed_at',
        'amount', 'payment_status',
        'cancellation_reason', 'rating', 'review',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'scheduled_at' => 'datetime',
            'assigned_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'amount' => 'float',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->reference = 'COL-' . strtoupper(uniqid());
        });
    }

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function collector()
    {
        return $this->belongsTo(User::class, 'collector_id');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'En attente',
            'assigned' => 'Assigné',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
            'cancelled' => 'Annulé',
            'rejected' => 'Rejeté',
            default => $this->status,
        };
    }

    public function getWasteTypeLabelAttribute(): string
    {
        return match($this->waste_type) {
            'household' => 'Déchets ménagers',
            'organic' => 'Déchets organiques',
            'recyclable' => 'Recyclables',
            'hazardous' => 'Déchets dangereux',
            'construction' => 'Débris construction',
            'other' => 'Autre',
            default => $this->waste_type,
        };
    }
}
