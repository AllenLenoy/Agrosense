<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'name', 'area_acres', 'soil_type', 'current_crop', 'status', 'geojson'
    ];

    protected $casts = [
        'area_acres' => 'decimal:2',
        'geojson' => 'array',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function crops()
    {
        return $this->hasMany(Crop::class);
    }

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function irrigationLogs()
    {
        return $this->hasMany(IrrigationLog::class);
    }
}
