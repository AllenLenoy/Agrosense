<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiseaseReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'farm_id', 'crop_id', 'image_path', 'disease_name', 'confidence',
        'severity', 'description', 'treatment', 'prevention', 'status',
    ];

    protected $casts = [
        'confidence' => 'decimal:2',
    ];

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    public function crop()
    {
        return $this->belongsTo(Crop::class);
    }

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
