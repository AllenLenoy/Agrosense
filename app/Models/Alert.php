<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'sensor_id', 'title', 'message', 'type',
        'category', 'priority', 'severity', 'is_read', 'is_resolved', 'resolved_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }
}
