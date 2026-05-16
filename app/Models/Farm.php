<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'description', 'location', 'latitude', 'longitude',
        'area_acres', 'soil_type', 'image', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'area_acres' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user() — used by AdminController.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class);
    }

    public function irrigationLogs()
    {
        return $this->hasMany(IrrigationLog::class);
    }

    public function irrigationSchedules()
    {
        return $this->hasMany(IrrigationSchedule::class);
    }

    public function weatherData()
    {
        return $this->hasMany(WeatherData::class);
    }

    public function diseaseReports()
    {
        return $this->hasMany(DiseaseReport::class);
    }

    /**
     * Alias for diseaseReports() — used by ReportController.
     */
    public function diseases()
    {
        return $this->hasMany(DiseaseReport::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
