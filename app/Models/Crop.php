<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'field_id', 'name', 'variety', 'planted_at', 'expected_harvest',
        'harvested_at', 'status', 'health_score', 'expected_yield_kg',
        'actual_yield_kg', 'notes',
    ];

    protected $casts = [
        'planted_at' => 'date',
        'expected_harvest' => 'date',
        'harvested_at' => 'date',
        'expected_yield_kg' => 'decimal:2',
        'actual_yield_kg' => 'decimal:2',
    ];

    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    public function diseaseReports()
    {
        return $this->hasMany(DiseaseReport::class);
    }

    public function getHealthStatusAttribute(): string
    {
        if ($this->health_score >= 80) return 'healthy';
        if ($this->health_score >= 50) return 'moderate';
        return 'critical';
    }
}
