<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $farms = $user->isAdmin() ? \App\Models\Farm::all() : $user->farms;
        $activeFarm = $farms->first();

        $recommendations = $this->generateRecommendations($activeFarm);

        return view('recommendations.index', compact('recommendations', 'farms', 'activeFarm'));
    }

    private function generateRecommendations($farm)
    {
        if (!$farm) return collect();

        $recommendations = collect();

        // 1. Irrigation/Moisture Recommendation
        $soilSensors = $farm->sensors()->where('type', 'soil_moisture')->get();
        if ($soilSensors->count() > 0) {
            $avgMoisture = 0;
            $count = 0;
            foreach ($soilSensors as $sensor) {
                if ($sensor->latestReading && $sensor->latestReading->soil_moisture !== null) {
                    $avgMoisture += $sensor->latestReading->soil_moisture;
                    $count++;
                }
            }
            if ($count > 0) {
                $avgMoisture /= $count;
                if ($avgMoisture < 30) {
                    $recommendations->push([
                        'category' => 'irrigation',
                        'icon' => 'droplet',
                        'title' => 'Critical: Immediate Irrigation Required',
                        'description' => 'Average soil moisture has dropped to ' . round($avgMoisture) . '%. Immediate drip irrigation is recommended to prevent drought stress.',
                        'priority' => 'high',
                        'confidence' => 95,
                    ]);
                } elseif ($avgMoisture > 75) {
                    $recommendations->push([
                        'category' => 'irrigation',
                        'icon' => 'droplet',
                        'title' => 'Halt Irrigation',
                        'description' => 'Soil moisture is currently saturated (' . round($avgMoisture) . '%). Pause all scheduled irrigation to avoid root rot.',
                        'priority' => 'medium',
                        'confidence' => 90,
                    ]);
                } else {
                    $recommendations->push([
                        'category' => 'irrigation',
                        'icon' => 'droplet',
                        'title' => 'Optimal Moisture Levels',
                        'description' => 'Current soil moisture is optimal (' . round($avgMoisture) . '%). Maintain current irrigation schedule.',
                        'priority' => 'low',
                        'confidence' => 88,
                    ]);
                }
            }
        }

        // 2. Weather Recommendation
        $latestWeather = $farm->weatherData()->latest('recorded_at')->first();
        if ($latestWeather) {
            if ($latestWeather->temperature > 35 && $latestWeather->humidity < 40) {
                $recommendations->push([
                    'category' => 'weather',
                    'icon' => 'cloud-sun',
                    'title' => 'Heat Stress Warning',
                    'description' => 'High temperatures (' . round($latestWeather->temperature) . '°C) and low humidity detected. Apply protective shading if possible and increase irrigation frequency.',
                    'priority' => 'high',
                    'confidence' => 85,
                ]);
            } elseif ($latestWeather->rainfall_mm > 20) {
                $recommendations->push([
                    'category' => 'weather',
                    'icon' => 'cloud-sun',
                    'title' => 'Heavy Rainfall Detected',
                    'description' => 'Recent heavy rainfall (' . $latestWeather->rainfall_mm . 'mm) recorded. Ensure proper drainage in fields to prevent waterlogging.',
                    'priority' => 'medium',
                    'confidence' => 92,
                ]);
            }
        }

        // 3. Disease Recommendation
        $recentDiseases = $farm->diseaseReports()->where('status', 'pending')->where('severity', 'critical')->count();
        if ($recentDiseases > 0) {
            $recommendations->push([
                'category' => 'disease',
                'icon' => 'shield',
                'title' => 'Immediate Disease Treatment Needed',
                'description' => "You have {$recentDiseases} untreated critical disease reports. Please review the AI treatments and apply fungicides immediately.",
                'priority' => 'high',
                'confidence' => 98,
            ]);
        }

        // 4. Crop & Soil Recommendations (Fallback defaults for empty conditions)
        if ($recommendations->count() < 3) {
            $recommendations->push([
                'category' => 'soil',
                'icon' => 'layer-group',
                'title' => 'Increase Organic Matter',
                'description' => 'Regular application of organic compost will improve soil structure and water retention capacity.',
                'priority' => 'low',
                'confidence' => 85,
            ]);
            
            $recommendations->push([
                'category' => 'crop',
                'icon' => 'seedling',
                'title' => 'Optimal Crop Rotation',
                'description' => 'Planning to rotate with leguminous crops in the next cycle will help naturally restore soil nitrogen levels.',
                'priority' => 'low',
                'confidence' => 92,
            ]);
        }

        return $recommendations->sortByDesc(function($rec) {
            return $rec['priority'] === 'high' ? 3 : ($rec['priority'] === 'medium' ? 2 : 1);
        })->values();
    }
}
