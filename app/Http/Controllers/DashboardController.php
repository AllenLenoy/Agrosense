<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\Alert;
use App\Models\IrrigationLog;
use App\Models\Crop;
use App\Models\WeatherData;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $farms = $user->isAdmin()
            ? Farm::with('sensors', 'fields.crops')->get()
            : $user->farms()->with('sensors', 'fields.crops')->get();

        $activeFarm = $farms->first();

        if (!$activeFarm) {
            return view('dashboard.index', [
                'farms' => $farms,
                'activeFarm' => null,
                'stats' => [],
                'recentAlerts' => collect(),
                'recentActivity' => collect(),
                'sensorData' => [],
                'irrigationActive' => null,
                'weather' => null,
                'crops' => collect(),
                'sensors' => collect(),
                'chartData' => ['labels' => [], 'moisture' => [], 'temperature' => [], 'humidity' => []],
            ]);
        }

        // Get latest sensor readings
        $sensors = $activeFarm->sensors()->with('latestReading')->get();
        $sensorData = $this->aggregateSensorData($sensors);

        // Stats
        $stats = [
            'total_farms' => $farms->count(),
            'total_sensors' => $sensors->count(),
            'online_sensors' => $sensors->where('status', 'online')->count(),
            'total_fields' => $activeFarm->fields->count(),
            'total_crops' => $activeFarm->fields->sum(fn($f) => $f->crops->count()),
            'active_alerts' => $activeFarm->alerts()->unread()->count(),
            'avg_soil_moisture' => $sensorData['soil_moisture'] ?? 0,
            'avg_temperature' => $sensorData['temperature'] ?? 0,
            'avg_humidity' => $sensorData['humidity'] ?? 0,
            'water_level' => $sensorData['water_level'] ?? 0,
        ];

        // Recent alerts
        $recentAlerts = $activeFarm->alerts()
            ->latest()
            ->take(5)
            ->get();

        // Active irrigation
        $irrigationActive = IrrigationLog::where('farm_id', $activeFarm->id)
            ->where('status', 'active')
            ->first();

        // Weather
        $weather = WeatherData::where('farm_id', $activeFarm->id)
            ->latest('recorded_at')
            ->first();

        // Crops
        $crops = Crop::whereIn('field_id', $activeFarm->fields->pluck('id'))
            ->get();

        // Recent activity
        $recentActivity = ActivityLog::where('farm_id', $activeFarm->id)
            ->latest()
            ->take(8)
            ->get();

        // Chart data - last 24 hours
        $chartData = $this->getChartData($activeFarm);

        return view('dashboard.index', compact(
            'farms', 'activeFarm', 'stats', 'recentAlerts', 'recentActivity',
            'sensorData', 'irrigationActive', 'weather', 'crops', 'chartData', 'sensors'
        ));
    }

    private function aggregateSensorData($sensors)
    {
        $data = [
            'soil_moisture' => 0,
            'temperature' => 0,
            'humidity' => 0,
            'water_level' => 0,
            'light_intensity' => 0,
            'ph_level' => 0,
        ];

        $counts = array_fill_keys(array_keys($data), 0);

        foreach ($sensors as $sensor) {
            if (!$sensor->latestReading) continue;
            $reading = $sensor->latestReading;

            foreach ($data as $key => $val) {
                if ($reading->$key !== null) {
                    $data[$key] += (float) $reading->$key;
                    $counts[$key]++;
                }
            }
        }

        foreach ($data as $key => $val) {
            $data[$key] = $counts[$key] > 0 ? round($val / $counts[$key], 1) : 0;
        }

        return $data;
    }

    private function getChartData($farm)
    {
        $sensorIds = $farm->sensors()->pluck('id');
        $readings = SensorReading::whereIn('sensor_id', $sensorIds)
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at')
            ->get();

        $labels = [];
        $moisture = [];
        $temperature = [];
        $humidity = [];

        $grouped = $readings->groupBy(function ($item) {
            return $item->recorded_at->format('H:i');
        });

        foreach ($grouped as $time => $group) {
            $labels[] = $time;
            $moisture[] = round($group->whereNotNull('soil_moisture')->avg('soil_moisture'), 1);
            $temperature[] = round($group->whereNotNull('temperature')->avg('temperature'), 1);
            $humidity[] = round($group->whereNotNull('humidity')->avg('humidity'), 1);
        }

        return [
            'labels' => $labels,
            'moisture' => $moisture,
            'temperature' => $temperature,
            'humidity' => $humidity,
        ];
    }
}
