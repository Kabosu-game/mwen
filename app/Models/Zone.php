<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'commune', 'department', 'description',
        'is_active', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function collectionRequests()
    {
        return $this->hasMany(CollectionRequest::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function missions()
    {
        return $this->hasMany(Mission::class);
    }
}
