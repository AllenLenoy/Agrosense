<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Sensor;
use App\Models\SensorReading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SensorController extends Controller
{
    // ── Sensor catalog ────────────────────────────────────────────────────────
    // Real agricultural sensors farmers commonly use with ESP32/Arduino setups.
    // Each entry auto-fills the pairing form when selected.
    public const CATALOG = [
        [
            'id'          => 'capacitive-v1.2',
            'name'        => 'Capacitive Soil Moisture v1.2',
            'type'        => 'soil_moisture',
            'brand'       => 'Generic / DIY',
            'protocol'    => 'Analog (0–3.3V)',
            'measures'    => 'Soil moisture %',
            'range'       => '0–100%',
            'description' => 'Most popular low-cost soil moisture sensor. Capacitive design resists corrosion. Works directly with ESP32 ADC pin.',
            'icon'        => 'tint',
            'color'       => '#3b82f6',
        ],
        [
            'id'          => 'dht22',
            'name'        => 'DHT22 (AM2302)',
            'type'        => 'humidity',
            'brand'       => 'Aosong',
            'protocol'    => 'Single-wire digital',
            'measures'    => 'Temperature + Humidity',
            'range'       => '-40–80°C / 0–100% RH',
            'description' => 'Industry-standard temp/humidity sensor. More accurate than DHT11. Widely used in weather stations and greenhouses.',
            'icon'        => 'cloud-rain',
            'color'       => '#06b6d4',
        ],
        [
            'id'          => 'dht11',
            'name'        => 'DHT11',
            'type'        => 'humidity',
            'brand'       => 'Aosong',
            'protocol'    => 'Single-wire digital',
            'measures'    => 'Temperature + Humidity',
            'range'       => '0–50°C / 20–90% RH',
            'description' => 'Budget-friendly temp/humidity sensor. Good for basic monitoring. Lower accuracy than DHT22 but very affordable.',
            'icon'        => 'cloud-rain',
            'color'       => '#06b6d4',
        ],
        [
            'id'          => 'ds18b20',
            'name'        => 'DS18B20 Waterproof Temp Probe',
            'type'        => 'temperature',
            'brand'       => 'Dallas / Maxim',
            'protocol'    => '1-Wire digital',
            'measures'    => 'Soil / Water temperature',
            'range'       => '-55–125°C (±0.5°C)',
            'description' => 'Waterproof stainless steel probe. Ideal for measuring soil temperature at depth or water temperature in irrigation tanks.',
            'icon'        => 'thermometer-half',
            'color'       => '#f97316',
        ],
        [
            'id'          => 'ph-4502c',
            'name'        => 'pH Sensor Module (PH-4502C)',
            'type'        => 'ph_sensor',
            'brand'       => 'Generic',
            'protocol'    => 'Analog (0–3.3V)',
            'measures'    => 'Soil / Water pH',
            'range'       => 'pH 0–14 (±0.1)',
            'description' => 'Analog pH electrode with signal conditioning board. Requires calibration with pH 4.0 and 7.0 buffer solutions.',
            'icon'        => 'flask',
            'color'       => '#8b5cf6',
        ],
        [
            'id'          => 'hc-sr04',
            'name'        => 'HC-SR04 Ultrasonic (Water Level)',
            'type'        => 'water_level',
            'brand'       => 'Generic',
            'protocol'    => 'Digital trigger/echo',
            'measures'    => 'Water tank level %',
            'range'       => '2cm–400cm distance',
            'description' => 'Ultrasonic distance sensor mounted above a water tank. Calculates fill level by measuring distance to water surface.',
            'icon'        => 'water',
            'color'       => '#22c55e',
        ],
        [
            'id'          => 'jsnsr04t',
            'name'        => 'JSN-SR04T Waterproof Ultrasonic',
            'type'        => 'water_level',
            'brand'       => 'Generic',
            'protocol'    => 'Digital trigger/echo',
            'measures'    => 'Water level (outdoor)',
            'range'       => '20cm–600cm',
            'description' => 'Waterproof version of HC-SR04. Suitable for outdoor water tanks, canals, and open reservoirs.',
            'icon'        => 'water',
            'color'       => '#22c55e',
        ],
        [
            'id'          => 'bmp280',
            'name'        => 'BMP280 Barometric Pressure',
            'type'        => 'weather_station',
            'brand'       => 'Bosch',
            'protocol'    => 'I2C / SPI',
            'measures'    => 'Pressure + Temperature + Altitude',
            'range'       => '300–1100 hPa / -40–85°C',
            'description' => 'Precision barometric sensor. Used in weather stations to detect incoming storms and altitude-based temperature correction.',
            'icon'        => 'gauge-high',
            'color'       => '#f59e0b',
        ],
        [
            'id'          => 'bh1750',
            'name'        => 'BH1750 Light Intensity Sensor',
            'type'        => 'light',
            'brand'       => 'ROHM',
            'protocol'    => 'I2C',
            'measures'    => 'Ambient light (lux)',
            'range'       => '1–65535 lux',
            'description' => 'Digital light sensor for measuring solar radiation levels. Useful for optimising greenhouse lighting and detecting shading.',
            'icon'        => 'sun',
            'color'       => '#eab308',
        ],
        [
            'id'          => 'npk-rs485',
            'name'        => 'NPK Soil Nutrient Sensor (RS485)',
            'type'        => 'soil_moisture',
            'brand'       => 'JXCT / Generic',
            'protocol'    => 'RS485 Modbus RTU',
            'measures'    => 'Nitrogen, Phosphorus, Potassium',
            'range'       => '0–1999 mg/kg',
            'description' => 'Measures soil NPK levels directly. Requires RS485-to-TTL converter with ESP32. Provides fertiliser recommendations.',
            'icon'        => 'layer-group',
            'color'       => '#84cc16',
        ],
        [
            'id'          => 'rain-gauge',
            'name'        => 'Tipping Bucket Rain Gauge',
            'type'        => 'rainfall',
            'brand'       => 'Generic',
            'protocol'    => 'Digital pulse',
            'measures'    => 'Rainfall (mm)',
            'range'       => '0–999mm (0.2mm/tip)',
            'description' => 'Each tip of the bucket = 0.2mm of rainfall. Connect to ESP32 interrupt pin. Essential for irrigation scheduling.',
            'icon'        => 'cloud-showers-heavy',
            'color'       => '#60a5fa',
        ],
        [
            'id'          => 'anemometer',
            'name'        => 'Wind Speed Anemometer',
            'type'        => 'wind',
            'brand'       => 'Generic',
            'protocol'    => 'Analog / Pulse',
            'measures'    => 'Wind speed (km/h)',
            'range'       => '0–30 m/s',
            'description' => 'Cup anemometer for measuring wind speed. Important for spray timing decisions and evapotranspiration calculations.',
            'icon'        => 'wind',
            'color'       => '#94a3b8',
        ],
    ];

    // ── Index ─────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $user    = Auth::user();
        $farmIds = $user->isAdmin()
            ? Farm::pluck('id')
            : $user->farms()->pluck('id');

        $farms = $user->isAdmin() ? Farm::all() : $user->farms;

        $query = Sensor::whereIn('farm_id', $farmIds)
            ->with(['farm', 'field', 'latestReading']);

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
        $catalog = self::CATALOG;

        return view('sensors.index', compact('sensors', 'farmIds', 'farms', 'catalog'));
    }

    // ── Show ──────────────────────────────────────────────────────────────────
    public function show(Sensor $sensor)
    {
        $this->authoriseSensor($sensor);

        $sensor->load(['farm', 'field', 'latestReading']);

        $readings = $sensor->readings()
            ->where('recorded_at', '>=', now()->subHours(24))
            ->orderBy('recorded_at')
            ->get();

        $chartData      = $this->buildChartData($sensor, $readings);
        $recentReadings = $sensor->readings()->latest('recorded_at')->take(20)->get();

        return view('sensors.show', compact('sensor', 'chartData', 'recentReadings'));
    }

    // ── Store (from farm page) ────────────────────────────────────────────────
    public function store(Request $request, Farm $farm)
    {
        $request->validate([
            'device_id' => 'required|string|max:255|unique:sensors,device_id',
            'name'      => 'required|string|max:255',
            'type'      => 'required|string',
            'field_id'  => 'nullable|exists:fields,id',
            'model'     => 'nullable|string|max:255',
        ]);

        if (! $request->user()->isAdmin() &&
            ! $request->user()->farms()->where('farms.id', $farm->id)->exists()) {
            abort(403);
        }

        $sensor = $farm->sensors()->create([
            'device_id'    => $request->device_id,
            'name'         => $request->name,
            'type'         => $request->type,
            'field_id'     => $request->field_id,
            'model'        => $request->model,
            'status'       => 'offline',
            'battery_level'=> 100,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'farm_id' => $farm->id,
            'action' => 'sensor.pair',
            'description' => Auth::user()->name . ' paired sensor: ' . $sensor->name,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('farms.show', $farm)
            ->with('success', 'Sensor paired successfully. It will appear online once it transmits data.');
    }

    // ── Store from sensors index page ─────────────────────────────────────────
    public function storeFromIndex(Request $request)
    {
        $request->validate([
            'farm_id'   => 'required|exists:farms,id',
            'device_id' => 'required|string|max:255|unique:sensors,device_id',
            'name'      => 'required|string|max:255',
            'type'      => 'required|string',
            'field_id'  => 'nullable|exists:fields,id',
            'model'     => 'nullable|string|max:255',
        ]);

        $farm = Farm::findOrFail($request->farm_id);

        if (! $request->user()->isAdmin() &&
            ! $request->user()->farms()->where('farms.id', $farm->id)->exists()) {
            abort(403);
        }

        $sensor = $farm->sensors()->create([
            'device_id'    => $request->device_id,
            'name'         => $request->name,
            'type'         => $request->type,
            'field_id'     => $request->field_id,
            'model'        => $request->model,
            'status'       => 'offline',
            'battery_level'=> 100,
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'farm_id' => $farm->id,
            'action' => 'sensor.pair',
            'description' => Auth::user()->name . ' paired sensor: ' . $sensor->name,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('sensors.index')
            ->with('success', 'Sensor paired successfully.');
    }

    // ── Update ────────────────────────────────────────────────────────────────
    public function update(Request $request, Sensor $sensor)
    {
        $this->authoriseSensor($sensor);

        $request->validate([
            'name'     => 'required|string|max:255',
            'type'     => 'required|string',
            'field_id' => 'nullable|exists:fields,id',
            'model'    => 'nullable|string|max:255',
        ]);

        $sensor->update($request->only('name', 'type', 'field_id', 'model'));

        return redirect()->route('sensors.index')
            ->with('success', 'Sensor updated successfully.');
    }

    // ── Destroy ───────────────────────────────────────────────────────────────
    public function destroy(Sensor $sensor)
    {
        $this->authoriseSensor($sensor);
        $sensor->delete();

        return redirect()->route('sensors.index')
            ->with('success', 'Sensor removed.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function authoriseSensor(Sensor $sensor): void
    {
        $user = Auth::user();
        if ($user->isAdmin()) return;
        if (! $user->farms()->where('farms.id', $sensor->farm_id)->exists()) {
            abort(403);
        }
    }

    private function buildChartData(Sensor $sensor, $readings): array
    {
        $labels   = [];
        $datasets = [];
        $fields   = ['soil_moisture','temperature','humidity','water_level','light_intensity','ph_level'];

        foreach ($readings as $r) {
            $labels[] = $r->recorded_at->format('H:i');
        }
        foreach ($fields as $field) {
            $values = $readings->pluck($field)->values();
            // Only include a dataset if at least one non-null value exists
            if ($values->filter()->isNotEmpty()) {
                $datasets[$field] = $values->map(fn ($v) => $v !== null ? round((float) $v, 1) : null)->toArray();
            }
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }
}
