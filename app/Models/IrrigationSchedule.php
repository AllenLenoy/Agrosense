<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IrrigationSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'field_id', 'name', 'start_time', 'duration_minutes',
        'days_of_week', 'moisture_threshold', 'is_active', 'is_smart',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
        'is_smart' => 'boolean',
        'moisture_threshold' => 'decimal:2',
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
