<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MissionApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'mission_id', 'user_id', 'status',
        'motivation', 'accepted_at', 'completed_at',
        'rating', 'review', 'payment_sent',
    ];

    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'completed_at' => 'datetime',
            'payment_sent' => 'boolean',
        ];
    }

    public function mission()
    {
        return $this->belongsTo(Mission::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
