<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'field_id', 'device_id', 'name', 'type', 'status',
        'model', 'firmware_version', 'battery_level', 'last_reading_at', 'config',
    ];

    protected $casts = [
        'last_reading_at' => 'datetime',
        'battery_level' => 'decimal:2',
        'config' => 'array',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function readings()
    {
        return $this->hasMany(SensorReading::class);
    }

    public function latestReading()
    {
        return $this->hasOne(SensorReading::class)->latestOfMany('recorded_at');
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }
}
