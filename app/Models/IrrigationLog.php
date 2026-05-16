<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrrigationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'field_id', 'type', 'status', 'started_at',
        'ended_at', 'duration_minutes', 'water_used_liters',
        'trigger_reason', 'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'water_used_liters' => 'decimal:2',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }
}
