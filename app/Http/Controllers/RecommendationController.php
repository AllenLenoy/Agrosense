<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\IrrigationLog;
use App\Models\SensorReading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class RecommendationController extends Controller
{
    // ── Crop-specific ideal ranges ────────────────────────────────────────────
    // [moisture_min, moisture_max, temp_min, temp_max, ph_min, ph_max]
    private const CROP_PROFILES = [
        'Rice'        => ['moisture' => [60, 85], 'temp' => [20, 35], 'ph' => [5.5, 7.0]],
        'Wheat'       => ['moisture' => [40, 65], 'temp' => [12, 25], 'ph' => [6.0, 7.5]],
        'Maize'       => ['moisture' => [45, 70], 'temp' => [18, 32], 'ph' => [5.8, 7.0]],
        'Maize (Corn)'=> ['moisture' => [45, 70], 'temp' => [18, 32], 'ph' => [5.8, 7.0]],
        'Sugarcane'   => ['moisture' => [55, 80], 'temp' => [20, 38], 'ph' => [6.0, 7.5]],
        'Cotton'      => ['moisture' => [35, 60], 'temp' => [20, 35], 'ph' => [5.8, 8.0]],
        'Soybean'     => ['moisture' => [40, 65], 'temp' => [15, 30], 'ph' => [6.0, 7.0]],
        'Potato'      => ['moisture' => [50, 70], 'temp' => [10, 25], 'ph' => [4.8, 6.5]],
        'Tomato'      => ['moisture' => [45, 70], 'temp' => [18, 30], 'ph' => [5.5, 7.0]],
        'Onion'       => ['moisture' => [35, 55], 'temp' => [13, 28], 'ph' => [6.0, 7.5]],
        'Chickpea'    => ['moisture' => [30, 55], 'temp' => [15, 30], 'ph' => [6.0, 8.0]],
        'Lentil'      => ['moisture' => [30, 55], 'temp' => [15, 28], 'ph' => [6.0, 8.0]],
        'Groundnut'   => ['moisture' => [40, 65], 'temp' => [22, 35], 'ph' => [5.5, 7.0]],
        'Sunflower'   => ['moisture' => [35, 60], 'temp' => [18, 35], 'ph' => [6.0, 7.5]],
        'Barley'      => ['moisture' => [35, 60], 'temp' => [10, 25], 'ph' => [6.0, 8.0]],
        'Millet'      => ['moisture' => [25, 50], 'temp' => [20, 38], 'ph' => [5.5, 7.5]],
        'Banana'      => ['moisture' => [60, 80], 'temp' => [22, 35], 'ph' => [5.5, 7.0]],
        'Mango'       => ['moisture' => [40, 65], 'temp' => [24, 38], 'ph' => [5.5, 7.5]],
        'Grapes'      => ['moisture' => [35, 60], 'temp' => [15, 35], 'ph' => [5.5, 7.0]],
        'Chilli Pepper'=>['moisture' => [45, 70], 'temp' => [18, 32], 'ph' => [6.0, 7.5]],
        // Default fallback
        'default'     => ['moisture' => [35, 70], 'temp' => [15, 35], 'ph' => [5.5, 7.5]],
    ];

    public function index(Request $request)
    {
        $user  = Auth::user();
        $farms = $user->isAdmin() ? Farm::all() : $user->farms;

        $activeFarmId = $request->query('farm_id');
        if ($activeFarmId) {
            session(['active_farm_id' => $activeFarmId]);
        } else {
            $activeFarmId = session('active_farm_id');
        }
        $activeFarm   = $activeFarmId
            ? $farms->firstWhere('id', $activeFarmId)
            : $farms->first();

        $recommendations = $this->generateRecommendations($activeFarm);

        return view('recommendations.index', compact('recommendations', 'farms', 'activeFarm'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    private function generateRecommendations(?Farm $farm): Collection
    {
        if (! $farm) return collect();

        $recommendations = collect();

        // ── Load all data upfront ─────────────────────────────────────────────
        $farm->load([
            'sensors.latestReading',
            'fields.crops',
            'weatherData' => fn ($q) => $q->latest('recorded_at')->limit(1),
            'irrigationLogs' => fn ($q) => $q->latest('started_at')->limit(5),
            'alerts' => fn ($q) => $q->where('is_resolved', false)->latest()->limit(10),
        ]);

        $latestWeather  = $farm->weatherData->first();
        $activeCrops    = $farm->fields->flatMap->crops->where('status', 'growing');
        $cropNames      = $activeCrops->pluck('name')->unique()->filter()->values();
        $sensors        = $farm->sensors;
        $sensorIds      = $sensors->pluck('id');

        // ── 1. SENSOR HEALTH ─────────────────────────────────────────────────
        $offlineSensors = $sensors->where('status', 'offline');
        if ($offlineSensors->count() > 0) {
            $recommendations->push([
                'category'    => 'sensor',
                'icon'        => 'wifi',
                'title'       => $offlineSensors->count() . ' Sensor(s) Offline',
                'description' => 'The following sensors are not transmitting: '
                    . $offlineSensors->pluck('name')->join(', ', ' and ')
                    . '. Data from these sensors is missing — check power and connectivity.',
                'priority'    => 'high',
                'confidence'  => 100,
                'action'      => 'Check sensors',
                'action_url'  => route('sensors.index'),
            ]);
        }

        $lowBatterySensors = $sensors->filter(fn ($s) => $s->battery_level !== null && $s->battery_level < 20);
        if ($lowBatterySensors->count() > 0) {
            $recommendations->push([
                'category'    => 'sensor',
                'icon'        => 'battery-quarter',
                'title'       => 'Low Battery on ' . $lowBatterySensors->count() . ' Sensor(s)',
                'description' => $lowBatterySensors->map(fn ($s) => "{$s->name} ({$s->battery_level}%)")->join(', ', ' and ')
                    . '. Replace or recharge batteries to avoid data gaps.',
                'priority'    => 'medium',
                'confidence'  => 100,
                'action'      => null,
                'action_url'  => null,
            ]);
        }

        // ── 2. SOIL MOISTURE — with trend detection ───────────────────────────
        $soilSensors = $sensors->where('type', 'soil_moisture')
            ->filter(fn ($s) => $s->latestReading && $s->latestReading->soil_moisture !== null);

        if ($soilSensors->count() > 0) {
            $avgMoisture = round($soilSensors->avg(fn ($s) => $s->latestReading->soil_moisture), 1);

            // Trend: compare last 3h average vs current
            $trend = $this->getMoistureTrend($sensorIds);

            // Crop-specific threshold
            $profile  = $this->getCropProfile($cropNames->first());
            $mMin     = $profile['moisture'][0];
            $mMax     = $profile['moisture'][1];
            $cropHint = $cropNames->isNotEmpty() ? ' for your ' . $cropNames->join(' & ') : '';

            if ($avgMoisture < $mMin) {
                $trendNote = $trend === 'falling' ? ' Moisture is actively declining — act now.' : '';
                $recommendations->push([
                    'category'    => 'irrigation',
                    'icon'        => 'tint',
                    'title'       => 'Low Soil Moisture' . ($trend === 'falling' ? ' & Declining' : ''),
                    'description' => "Soil moisture is {$avgMoisture}% — below the {$mMin}% minimum{$cropHint}. "
                        . "Start irrigation immediately to prevent drought stress.{$trendNote}",
                    'priority'    => $avgMoisture < ($mMin * 0.7) ? 'high' : 'medium',
                    'confidence'  => 95,
                    'action'      => 'Start Irrigation',
                    'action_url'  => route('irrigation.index'),
                ]);
            } elseif ($avgMoisture > $mMax) {
                $recommendations->push([
                    'category'    => 'irrigation',
                    'icon'        => 'tint',
                    'title'       => 'Soil Over-Saturated — Halt Irrigation',
                    'description' => "Soil moisture is {$avgMoisture}% — above the {$mMax}% maximum{$cropHint}. "
                        . 'Stop all irrigation immediately to prevent root rot and anaerobic conditions.',
                    'priority'    => 'high',
                    'confidence'  => 92,
                    'action'      => 'Manage Irrigation',
                    'action_url'  => route('irrigation.index'),
                ]);
            } else {
                $trendNote = $trend === 'falling'
                    ? ' Moisture is slowly declining — consider scheduling irrigation soon.'
                    : ($trend === 'rising' ? ' Moisture is rising — monitor before irrigating.' : '');
                $recommendations->push([
                    'category'    => 'irrigation',
                    'icon'        => 'tint',
                    'title'       => 'Soil Moisture Optimal',
                    'description' => "Soil moisture is {$avgMoisture}% — within the ideal {$mMin}–{$mMax}% range{$cropHint}.{$trendNote}",
                    'priority'    => 'low',
                    'confidence'  => 90,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }
        }

        // ── 3. TEMPERATURE — sensor + weather cross-check ────────────────────
        $tempSensors = $sensors->where('type', 'temperature')
            ->filter(fn ($s) => $s->latestReading && $s->latestReading->temperature !== null);

        $fieldTemp = $tempSensors->count() > 0
            ? round($tempSensors->avg(fn ($s) => $s->latestReading->temperature), 1)
            : ($latestWeather ? round($latestWeather->temperature, 1) : null);

        if ($fieldTemp !== null && $cropNames->isNotEmpty()) {
            $profile = $this->getCropProfile($cropNames->first());
            $tMin    = $profile['temp'][0];
            $tMax    = $profile['temp'][1];

            if ($fieldTemp > $tMax) {
                $recommendations->push([
                    'category'    => 'weather',
                    'icon'        => 'thermometer-full',
                    'title'       => 'Heat Stress Risk — ' . $fieldTemp . '°C',
                    'description' => "Temperature is {$fieldTemp}°C — above the {$tMax}°C safe limit for "
                        . $cropNames->join(' & ') . '. Apply shade nets, increase irrigation frequency, '
                        . 'and avoid fertiliser application during peak heat.',
                    'priority'    => 'high',
                    'confidence'  => 88,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            } elseif ($fieldTemp < $tMin) {
                $recommendations->push([
                    'category'    => 'weather',
                    'icon'        => 'thermometer-empty',
                    'title'       => 'Cold Stress Risk — ' . $fieldTemp . '°C',
                    'description' => "Temperature is {$fieldTemp}°C — below the {$tMin}°C minimum for "
                        . $cropNames->join(' & ') . '. Consider frost protection covers and reduce irrigation.',
                    'priority'    => 'medium',
                    'confidence'  => 85,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }
        }

        // ── 4. SOIL pH ────────────────────────────────────────────────────────
        $phSensors = $sensors->filter(
            fn ($s) => $s->latestReading && $s->latestReading->ph_level !== null
        );

        if ($phSensors->count() > 0 && $cropNames->isNotEmpty()) {
            $avgPh   = round($phSensors->avg(fn ($s) => $s->latestReading->ph_level), 1);
            $profile = $this->getCropProfile($cropNames->first());
            $phMin   = $profile['ph'][0];
            $phMax   = $profile['ph'][1];

            if ($avgPh < $phMin) {
                $recommendations->push([
                    'category'    => 'soil',
                    'icon'        => 'flask',
                    'title'       => 'Soil Too Acidic — pH ' . $avgPh,
                    'description' => "Soil pH is {$avgPh} — below the {$phMin} minimum for "
                        . $cropNames->join(' & ') . '. Apply agricultural lime (calcium carbonate) '
                        . 'at 1–2 tonnes/hectare to raise pH. Re-test after 4 weeks.',
                    'priority'    => 'medium',
                    'confidence'  => 90,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            } elseif ($avgPh > $phMax) {
                $recommendations->push([
                    'category'    => 'soil',
                    'icon'        => 'flask',
                    'title'       => 'Soil Too Alkaline — pH ' . $avgPh,
                    'description' => "Soil pH is {$avgPh} — above the {$phMax} maximum for "
                        . $cropNames->join(' & ') . '. Apply elemental sulphur or acidifying fertiliser. '
                        . 'Avoid over-liming in future.',
                    'priority'    => 'medium',
                    'confidence'  => 88,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }
        }

        // ── 5. WEATHER — rainfall, wind, UV ──────────────────────────────────
        if ($latestWeather) {
            if ($latestWeather->rainfall_mm > 25) {
                $recommendations->push([
                    'category'    => 'weather',
                    'icon'        => 'cloud-showers-heavy',
                    'title'       => 'Heavy Rainfall — Check Field Drainage',
                    'description' => round($latestWeather->rainfall_mm, 1) . 'mm of rain recorded at '
                        . $farm->name . '. Inspect drainage channels, delay any pesticide or fertiliser '
                        . 'application, and watch for waterlogging in low-lying fields.',
                    'priority'    => 'medium',
                    'confidence'  => 92,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }

            if ($latestWeather->wind_speed > 40) {
                $recommendations->push([
                    'category'    => 'weather',
                    'icon'        => 'wind',
                    'title'       => 'High Wind — Delay Spraying',
                    'description' => 'Wind speed is ' . round($latestWeather->wind_speed) . ' km/h. '
                        . 'Do not apply pesticides or foliar fertilisers — spray drift will reduce '
                        . 'effectiveness and may damage neighbouring crops.',
                    'priority'    => 'medium',
                    'confidence'  => 85,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }

            if ($latestWeather->uv_index !== null && $latestWeather->uv_index > 8) {
                $recommendations->push([
                    'category'    => 'weather',
                    'icon'        => 'sun',
                    'title'       => 'Extreme UV — Protect Young Crops',
                    'description' => 'UV index is ' . round($latestWeather->uv_index, 1) . ' (extreme). '
                        . 'Young seedlings and transplants are at risk of sunscald. '
                        . 'Use shade cloth and irrigate in the early morning.',
                    'priority'    => 'medium',
                    'confidence'  => 80,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }
        }

        // ── 6. IRRIGATION HISTORY — overwatering / underwatering pattern ─────
        $recentIrrigation = $farm->irrigationLogs
            ->where('started_at', '>=', now()->subDays(7))
            ->where('status', 'completed');

        if ($recentIrrigation->count() > 0) {
            $totalWater = $recentIrrigation->sum('water_used_liters');
            $totalArea  = $farm->fields->sum('area_acres') ?: 1;
            $litersPerAcre = round($totalWater / $totalArea);

            if ($litersPerAcre > 15000) {
                $recommendations->push([
                    'category'    => 'irrigation',
                    'icon'        => 'tint-slash',
                    'title'       => 'Possible Over-Irrigation This Week',
                    'description' => "Your farm used {$litersPerAcre} L/acre over the last 7 days — "
                        . 'above the typical 8,000–12,000 L/acre range. Review your irrigation schedule '
                        . 'to reduce water waste and prevent nutrient leaching.',
                    'priority'    => 'low',
                    'confidence'  => 75,
                    'action'      => 'Review Schedule',
                    'action_url'  => route('irrigation.index'),
                ]);
            }
        }

        // ── 7. CROP HEALTH & HARVEST TIMING ──────────────────────────────────
        foreach ($activeCrops as $crop) {
            // Low health score
            if ($crop->health_score !== null && $crop->health_score < 50) {
                $recommendations->push([
                    'category'    => 'crop',
                    'icon'        => 'seedling',
                    'title'       => "Poor Health: {$crop->name}" . ($crop->variety ? " ({$crop->variety})" : ''),
                    'description' => "Health score is {$crop->health_score}/100. "
                        . 'Inspect for pest damage, nutrient deficiency, or water stress. '
                        . 'Consider a foliar micronutrient spray if no visible pest damage is found.',
                    'priority'    => 'high',
                    'confidence'  => 90,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }

            // Harvest approaching within 14 days
            if ($crop->expected_harvest && $crop->expected_harvest->diffInDays(now(), false) >= -14
                && $crop->expected_harvest->isFuture()) {
                $days = $crop->expected_harvest->diffInDays(now());
                $recommendations->push([
                    'category'    => 'crop',
                    'icon'        => 'calendar-check',
                    'title'       => "Harvest Due in {$days} Day(s): {$crop->name}",
                    'description' => "{$crop->name}" . ($crop->variety ? " ({$crop->variety})" : '')
                        . " is due for harvest on {$crop->expected_harvest->format('d M Y')}. "
                        . 'Prepare harvesting equipment, arrange storage, and reduce irrigation '
                        . '5–7 days before harvest to improve quality.',
                    'priority'    => $days <= 3 ? 'high' : 'medium',
                    'confidence'  => 95,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }

            // Overdue harvest
            if ($crop->expected_harvest && $crop->expected_harvest->isPast()
                && $crop->status === 'growing') {
                $overdueDays = abs($crop->expected_harvest->diffInDays(now()));
                $recommendations->push([
                    'category'    => 'crop',
                    'icon'        => 'exclamation-triangle',
                    'title'       => "Overdue Harvest: {$crop->name} ({$overdueDays}d late)",
                    'description' => "{$crop->name} was expected to be harvested on "
                        . "{$crop->expected_harvest->format('d M Y')}. "
                        . 'Delayed harvest risks quality loss, pest infestation, and field blocking. '
                        . 'Harvest immediately if conditions allow.',
                    'priority'    => 'high',
                    'confidence'  => 98,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            }
        }

        // ── 8. UNRESOLVED HIGH-PRIORITY ALERTS ───────────────────────────────
        $criticalAlerts = $farm->alerts
            ->whereIn('type', ['critical', 'warning'])
            ->where('is_resolved', false);

        if ($criticalAlerts->count() > 0) {
            $recommendations->push([
                'category'    => 'alert',
                'icon'        => 'bell',
                'title'       => $criticalAlerts->count() . ' Unresolved Critical Alert(s)',
                'description' => 'You have ' . $criticalAlerts->count() . ' unresolved high-priority alerts: '
                    . $criticalAlerts->take(3)->pluck('title')->join(', ', ' and ')
                    . ($criticalAlerts->count() > 3 ? ' and more.' : '.'),
                'priority'    => 'high',
                'confidence'  => 100,
                'action'      => 'View Alerts',
                'action_url'  => route('alerts.index'),
            ]);
        }

        // ── 9. CROP-AWARE FALLBACK (pad to minimum 3 cards) ──────────────────
        if ($recommendations->count() < 3) {
            if ($cropNames->isNotEmpty()) {
                $recommendations->push([
                    'category'    => 'soil',
                    'icon'        => 'layer-group',
                    'title'       => 'Soil Health for ' . $cropNames->join(' & '),
                    'description' => 'Apply organic compost (2–4 tonnes/hectare) before the next planting cycle. '
                        . 'This improves water retention and nutrient availability — especially beneficial for '
                        . $cropNames->join(' and ') . '.',
                    'priority'    => 'low',
                    'confidence'  => 82,
                    'action'      => null,
                    'action_url'  => null,
                ]);
                $recommendations->push([
                    'category'    => 'crop',
                    'icon'        => 'sync-alt',
                    'title'       => 'Plan Crop Rotation After ' . $cropNames->join(' & '),
                    'description' => 'After harvesting ' . $cropNames->join(' and ')
                        . ', rotate with nitrogen-fixing legumes (beans, lentils, or chickpeas) '
                        . 'to restore soil fertility and break pest and disease cycles.',
                    'priority'    => 'low',
                    'confidence'  => 88,
                    'action'      => null,
                    'action_url'  => null,
                ]);
            } else {
                $recommendations->push([
                    'category'    => 'crop',
                    'icon'        => 'seedling',
                    'title'       => 'Add Crops to Your Fields',
                    'description' => 'No active crops found for ' . $farm->name
                        . '. Register your crops to unlock crop-specific moisture thresholds, '
                        . 'harvest timing alerts, and personalised soil advice.',
                    'priority'    => 'low',
                    'confidence'  => 100,
                    'action'      => 'Go to Farm',
                    'action_url'  => route('farms.show', $farm),
                ]);
            }
        }

        // Sort: high → medium → low
        return $recommendations->sortByDesc(fn ($r) => match ($r['priority']) {
            'high'   => 3,
            'medium' => 2,
            default  => 1,
        })->values();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function getCropProfile(string $cropName = null): array
    {
        if ($cropName && isset(self::CROP_PROFILES[$cropName])) {
            return self::CROP_PROFILES[$cropName];
        }
        return self::CROP_PROFILES['default'];
    }

    private function getMoistureTrend(Collection $sensorIds): string
    {
        if ($sensorIds->isEmpty()) return 'stable';

        $now     = now();
        $recent  = SensorReading::whereIn('sensor_id', $sensorIds)
            ->whereBetween('recorded_at', [$now->copy()->subHour(), $now])
            ->whereNotNull('soil_moisture')
            ->avg('soil_moisture');

        $earlier = SensorReading::whereIn('sensor_id', $sensorIds)
            ->whereBetween('recorded_at', [$now->copy()->subHours(4), $now->copy()->subHours(1)])
            ->whereNotNull('soil_moisture')
            ->avg('soil_moisture');

        if ($recent === null || $earlier === null) return 'stable';

        $diff = $recent - $earlier;
        if ($diff < -5)  return 'falling';
        if ($diff > 5)   return 'rising';
        return 'stable';
    }
}
