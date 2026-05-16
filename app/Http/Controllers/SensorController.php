<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SensorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $farmIds = $user->isAdmin()
            ? \App\Models\Farm::pluck('id')
            : $user->farms()->pluck('id');

        $query = Sensor::whereIn('farm_id', $farmIds)->with(['farm', 'field', 'latestReading']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('device_id', 'like', "%{$request->search}%");
            });
        }

        $sensors = $query->latest()->paginate(12)->withQueryString();

        return view('sensors.index', compact('sensors', 'farmIds'));
    }

    public function show(Sensor $sensor)
    {
        $sensor->load(['farm', 'field', 'latestReading']);

        $readings = $sensor->readings()
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at')
            ->get();

        $chartData = $this->buildChartData($sensor, $readings);

        $recentReadings = $sensor->readings()
            ->latest('recorded_at')
            ->take(20)
            ->get();

        return view('sensors.show', compact('sensor', 'chartData', 'recentReadings'));
    }

    private function buildChartData($sensor, $readings)
    {
        $labels = [];
        $datasets = [];

        $fields = ['soil_moisture', 'temperature', 'humidity', 'water_level', 'light_intensity', 'ph_level'];

        foreach ($readings as $reading) {
            $labels[] = $reading->recorded_at->format('H:i');
        }

        foreach ($fields as $field) {
            $values = $readings->pluck($field)->filter()->values();
            if ($values->isNotEmpty()) {
                $datasets[$field] = $values->map(fn($v) => round((float)$v, 1))->toArray();
            }
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
    }

    public function store(Request $request, \App\Models\Farm $farm)
    {
        $request->validate([
            'device_id' => 'required|string|max:255|unique:sensors,device_id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'field_id' => 'nullable|exists:fields,id',
        ]);

        // Ensure user can manage this farm
        if (!$request->user()->hasRole('Admin') && !$request->user()->farms()->where('farms.id', $farm->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $farm->sensors()->create([
            'device_id' => $request->device_id,
            'name' => $request->name,
            'type' => $request->type,
            'field_id' => $request->field_id,
            'status' => 'offline',
            'battery_level' => 100, // Default for new devices
        ]);

        return redirect()->route('farms.show', $farm)->with('success', 'IoT Device paired successfully. It is currently offline.');
    }
}
