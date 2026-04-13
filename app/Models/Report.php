<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'reporter_id', 'assigned_to', 'zone_id',
        'type', 'status', 'severity',
        'title', 'description', 'address',
        'latitude', 'longitude', 'photos',
        'admin_notes', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'resolved_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->reference = 'RPT-' . strtoupper(uniqid());
        });
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'illegal_dump' => 'Dépôt sauvage',
            'blocked_canal' => 'Canal bouché',
            'risk_zone' => 'Zone à risque',
            'flooding' => 'Inondation',
            'public_health' => 'Risque sanitaire',
            'other' => 'Autre',
            default => $this->type,
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'danger',
            default => 'secondary',
        };
    }
}
