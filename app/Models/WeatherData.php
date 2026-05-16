<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherData extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'temperature', 'humidity', 'wind_speed', 'wind_direction',
        'rainfall_mm', 'condition', 'icon', 'uv_index', 'visibility_km',
        'pressure_hpa', 'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'rainfall_mm' => 'decimal:2',
        'uv_index' => 'decimal:2',
        'pressure_hpa' => 'decimal:1',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
