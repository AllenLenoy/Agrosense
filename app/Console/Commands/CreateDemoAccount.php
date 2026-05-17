<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use App\Models\Crop;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\Alert;
use App\Models\IrrigationLog;
use App\Models\ActivityLog;
use App\Models\WeatherData;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CreateDemoAccount extends Command
{
    protected $signature = 'make:demo-account';
    protected $description = 'Creates a perfect demo account with fully populated data';

    public function handle()
    {
        $this->info('Creating Demo Account...');

        // 1. Create User
        $user = User::firstOrCreate(
            ['email' => 'demo@agrosense.in'],
            [
                'name' => 'Demo Farmer',
                'password' => Hash::make('password'),
                'role' => 'farmer',
            ]
        );
        $this->info("User created: demo@agrosense.in / password");

        // 2. Create Farm
        $farm = Farm::firstOrCreate(
            ['user_id' => $user->id, 'name' => 'AgroSense Model Farm'],
            [
                'location' => 'Ludhiana, Punjab',
                'size_acres' => 150.5,
                'latitude' => 30.900965,
                'longitude' => 75.857277,
            ]
        );

        // 3. Create Fields & Crops
        $fieldsData = [
            ['name' => 'North Wheat Sector', 'area_acres' => 50, 'soil_type' => 'Loamy', 'crop' => 'Wheat', 'stage' => 'growing'],
            ['name' => 'East Corn Plot', 'area_acres' => 45, 'soil_type' => 'Clay', 'crop' => 'Corn', 'stage' => 'mature'],
            ['name' => 'South Soybean Field', 'area_acres' => 55.5, 'soil_type' => 'Sandy Loam', 'crop' => 'Soybean', 'stage' => 'seedling'],
        ];

        $fields = [];
        foreach ($fieldsData as $fd) {
            $field = Field::firstOrCreate(
                ['farm_id' => $farm->id, 'name' => $fd['name']],
                ['area_acres' => $fd['area_acres'], 'soil_type' => $fd['soil_type']]
            );
            $fields[] = $field;

            Crop::firstOrCreate(
                ['field_id' => $field->id, 'name' => $fd['crop']],
                ['variety' => 'High Yield', 'planted_at' => now()->subDays(30), 'expected_harvest_date' => now()->addDays(90), 'status' => $fd['stage']]
            );
        }

        // 4. Create Sensors
        $sensorsData = [
            ['name' => 'Main Weather Station', 'type' => 'multi', 'field_id' => $fields[0]->id, 'device' => 'ST-WEATHER-01'],
            ['name' => 'Corn Moisture Probe', 'type' => 'soil_moisture', 'field_id' => $fields[1]->id, 'device' => 'ST-MOIST-02'],
            ['name' => 'Central Water Tank', 'type' => 'water_level', 'field_id' => null, 'device' => 'ST-TANK-03'],
            ['name' => 'Soil pH Analyzer', 'type' => 'ph', 'field_id' => $fields[2]->id, 'device' => 'ST-PH-04'],
        ];

        $sensors = [];
        foreach ($sensorsData as $sd) {
            $sensor = Sensor::firstOrCreate(
                ['device_id' => $sd['device']],
                [
                    'farm_id' => $farm->id,
                    'field_id' => $sd['field_id'],
                    'name' => $sd['name'],
                    'type' => $sd['type'],
                    'status' => 'online',
                    'battery_level' => rand(80, 100)
                ]
            );
            $sensors[] = $sensor;
            // Clear old readings if any
            $sensor->readings()->delete();
        }

        $this->info('Generating 24 hours of Sensor Readings...');

        // 5. Generate 24 hours of readings (every 30 mins = 48 readings)
        $now = now();
        for ($i = 48; $i >= 0; $i--) {
            $time = $now->copy()->subMinutes($i * 30);
            
            foreach ($sensors as $sensor) {
                // Base realistic values with some noise
                $moisture = 45 + (sin($i / 5) * 15) + rand(-2, 2);
                $temp = 22 + (cos($i / 8) * 8) + rand(-1, 1);
                $humidity = 60 + (sin($i / 10) * 20) + rand(-3, 3);
                $water = 85 - ($i * 0.2) + rand(-1, 1); // Slowly dropping
                $ph = 6.5 + rand(-1, 1) * 0.1;

                SensorReading::create([
                    'sensor_id' => $sensor->id,
                    'soil_moisture' => in_array($sensor->type, ['multi', 'soil_moisture']) ? min(100, max(0, $moisture)) : null,
                    'temperature' => $sensor->type === 'multi' ? $temp : null,
                    'humidity' => $sensor->type === 'multi' ? $humidity : null,
                    'water_level' => $sensor->type === 'water_level' ? min(100, max(0, $water)) : null,
                    'ph_level' => $sensor->type === 'ph_sensor' ? $ph : null,
                    'recorded_at' => $time,
                ]);
            }
            
            // Weather Data mock
            if ($i % 2 == 0) { // Every 1 hour
                WeatherData::updateOrCreate(
                    ['farm_id' => $farm->id, 'recorded_at' => $time],
                    [
                        'temperature' => 22 + (cos($i / 8) * 8),
                        'humidity' => 60 + (sin($i / 10) * 20),
                        'condition' => 'Clear',
                        'icon' => 'sun',
                        'wind_speed' => rand(5, 15),
                        'wind_direction' => 'NW',
                        'rainfall_mm' => 0,
                        'uv_index' => max(0, 8 * cos($i / 8)),
                        'pressure_hpa' => 1012 + rand(-2, 2),
                        'visibility_km' => 10,
                    ]
                );
            }
        }

        // 6. Active Irrigation Log
        $this->info('Creating Activity and Alerts...');
        IrrigationLog::where('farm_id', $farm->id)->delete();
        IrrigationLog::create([
            'farm_id' => $farm->id,
            'field_id' => $fields[0]->id,
            'type' => 'scheduled',
            'status' => 'active',
            'started_at' => now()->subMinutes(12),
            'trigger_reason' => 'Morning Routine Schedule',
        ]);
        IrrigationLog::create([
            'farm_id' => $farm->id,
            'field_id' => $fields[1]->id,
            'type' => 'manual',
            'status' => 'completed',
            'started_at' => now()->subHours(5),
            'ended_at' => now()->subHours(4)->subMinutes(30),
            'duration_minutes' => 30,
            'water_used_liters' => 1500,
            'trigger_reason' => 'Manual start by Demo Farmer',
        ]);

        // 7. Activity Logs
        ActivityLog::where('farm_id', $farm->id)->delete();
        ActivityLog::create(['user_id' => $user->id, 'farm_id' => $farm->id, 'action' => 'sensor.pair', 'description' => 'Demo Farmer paired sensor: Central Water Tank', 'ip_address' => '127.0.0.1', 'created_at' => now()->subDays(2)]);
        ActivityLog::create(['user_id' => $user->id, 'farm_id' => $farm->id, 'action' => 'irrigation.start', 'description' => 'Scheduled irrigation started on North Wheat Sector', 'ip_address' => '127.0.0.1', 'created_at' => now()->subMinutes(12)]);

        // 8. Alerts
        Alert::where('farm_id', $farm->id)->delete();
        Alert::create(['farm_id' => $farm->id, 'sensor_id' => $sensors[1]->id, 'title' => 'Low Moisture Detected', 'message' => 'Corn Moisture Probe reports moisture below 30%.', 'type' => 'warning', 'is_resolved' => false, 'created_at' => now()->subMinutes(45)]);
        Alert::create(['farm_id' => $farm->id, 'sensor_id' => $sensors[0]->id, 'title' => 'High Temperature', 'message' => 'Temperature exceeded 35°C yesterday.', 'type' => 'critical', 'is_resolved' => true, 'resolved_at' => now()->subHours(10), 'created_at' => now()->subHours(15)]);

        // Update latest reading pointers
        foreach ($sensors as $sensor) {
            $latest = $sensor->readings()->latest('recorded_at')->first();
            if ($latest) {
                $sensor->update(['last_reading_at' => $latest->recorded_at]);
            }
        }

        $this->info('Demo account created successfully!');
        $this->info('Email: demo@agrosense.in');
        $this->info('Password: password');
    }
}
