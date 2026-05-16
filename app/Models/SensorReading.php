<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'sensor_id', 'soil_moisture', 'temperature', 'humidity',
        'water_level', 'light_intensity', 'ph_level', 'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'soil_moisture' => 'decimal:2',
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'water_level' => 'decimal:2',
        'light_intensity' => 'decimal:2',
        'ph_level' => 'decimal:2',
    ];

    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}
