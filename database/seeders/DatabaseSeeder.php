<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use App\Models\Crop;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\IrrigationLog;
use App\Models\IrrigationSchedule;
use App\Models\Alert;
use App\Models\DiseaseReport;
use App\Models\WeatherData;
use App\Models\ActivityLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleAndPermissionSeeder::class);

        // Demo Accounts
        // Admin user
        $admin = User::create([
            'name' => 'Rajesh Kumar',
            'email' => 'admin@agrosense.in',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+91 98765 43210',
            'location' => 'Chandigarh, India',
            'bio' => 'Agricultural technology specialist with 15 years of experience in precision farming.',
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        // Farmer user
        $farmer = User::create([
            'name' => 'Gurpreet Singh',
            'email' => 'farmer@agrosense.in',
            'password' => Hash::make('password'),
            'role' => 'farmer',
            'phone' => '+91 99887 76655',
            'location' => 'Ludhiana, Punjab',
            'bio' => 'Third-generation farmer specializing in wheat and rice cultivation across 120 acres.',
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now()->subHours(2),
        ]);

        $farmer2 = User::create([
            'name' => 'Ananya Reddy',
            'email' => 'ananya@agrosense.in',
            'password' => Hash::make('password'),
            'role' => 'farmer',
            'phone' => '+91 87654 32100',
            'location' => 'Hyderabad, Telangana',
            'bio' => 'Modern organic farming practitioner focused on sustainable agriculture.',
            'is_active' => true,
            'email_verified_at' => now(),
            'last_login_at' => now()->subDay(),
        ]);

        // Create farms
        $farm1 = Farm::create([
            'user_id' => $farmer->id,
            'name' => 'Punjab Wheat Farm',
            'description' => 'Primary wheat and rice cultivation farm in the heart of Punjab. Equipped with modern irrigation systems and IoT sensors for precision agriculture.',
            'location' => 'Ludhiana, Punjab',
            'latitude' => 30.9010,
            'longitude' => 75.8573,
            'area_acres' => 85.50,
            'soil_type' => 'Alluvial',
            'is_active' => true,
        ]);

        $farm2 = Farm::create([
            'user_id' => $farmer->id,
            'name' => 'Amritsar Rice Paddies',
            'description' => 'Rice paddy fields with traditional flood irrigation upgraded to smart drip systems.',
            'location' => 'Amritsar, Punjab',
            'latitude' => 31.6340,
            'longitude' => 74.8723,
            'area_acres' => 42.00,
            'soil_type' => 'Clay Loam',
            'is_active' => true,
        ]);

        $farm3 = Farm::create([
            'user_id' => $farmer2->id,
            'name' => 'Telangana Cotton Fields',
            'description' => 'Organic cotton farming with precision monitoring and sustainable pest management.',
            'location' => 'Warangal, Telangana',
            'latitude' => 17.9784,
            'longitude' => 79.5941,
            'area_acres' => 60.00,
            'soil_type' => 'Black Cotton',
            'is_active' => true,
        ]);

        // Create fields for farm1
        $field1 = Field::create([
            'farm_id' => $farm1->id,
            'name' => 'North Block - A1',
            'area_acres' => 25.00,
            'soil_type' => 'Alluvial',
            'current_crop' => 'Wheat',
            'status' => 'active',
        ]);

        $field2 = Field::create([
            'farm_id' => $farm1->id,
            'name' => 'South Block - B2',
            'area_acres' => 30.00,
            'soil_type' => 'Alluvial Sandy',
            'current_crop' => 'Wheat',
            'status' => 'active',
        ]);

        $field3 = Field::create([
            'farm_id' => $farm1->id,
            'name' => 'East Block - C1',
            'area_acres' => 30.50,
            'soil_type' => 'Alluvial',
            'current_crop' => 'Mustard',
            'status' => 'active',
        ]);

        $field4 = Field::create([
            'farm_id' => $farm2->id,
            'name' => 'Paddy Section Alpha',
            'area_acres' => 22.00,
            'soil_type' => 'Clay Loam',
            'current_crop' => 'Rice',
            'status' => 'active',
        ]);

        $field5 = Field::create([
            'farm_id' => $farm2->id,
            'name' => 'Paddy Section Beta',
            'area_acres' => 20.00,
            'soil_type' => 'Clay Loam',
            'current_crop' => 'Rice',
            'status' => 'preparing',
        ]);

        // Create crops
        $crop1 = Crop::create([
            'field_id' => $field1->id,
            'name' => 'Wheat',
            'variety' => 'HD-3086',
            'planted_at' => Carbon::now()->subMonths(3),
            'expected_harvest' => Carbon::now()->addMonths(1),
            'status' => 'growing',
            'health_score' => 87,
            'expected_yield_kg' => 4200,
            'notes' => 'Strong growth pattern. Minor nutrient adjustment needed in week 8.',
        ]);

        $crop2 = Crop::create([
            'field_id' => $field2->id,
            'name' => 'Wheat',
            'variety' => 'PBW-725',
            'planted_at' => Carbon::now()->subMonths(4),
            'expected_harvest' => Carbon::now()->addWeeks(2),
            'status' => 'mature',
            'health_score' => 92,
            'expected_yield_kg' => 5100,
            'notes' => 'Excellent yield expected. Consistent moisture management.',
        ]);

        $crop3 = Crop::create([
            'field_id' => $field3->id,
            'name' => 'Mustard',
            'variety' => 'RH-749',
            'planted_at' => Carbon::now()->subMonths(2),
            'expected_harvest' => Carbon::now()->addMonths(2),
            'status' => 'growing',
            'health_score' => 74,
            'expected_yield_kg' => 1800,
            'notes' => 'Aphid pressure detected. Monitoring closely.',
        ]);

        $crop4 = Crop::create([
            'field_id' => $field4->id,
            'name' => 'Rice',
            'variety' => 'PR-126',
            'planted_at' => Carbon::now()->subMonths(2),
            'expected_harvest' => Carbon::now()->addMonths(2),
            'status' => 'growing',
            'health_score' => 81,
            'expected_yield_kg' => 3200,
        ]);

        // Create sensors for farm1
        $sensor1 = Sensor::create([
            'farm_id' => $farm1->id,
            'field_id' => $field1->id,
            'device_id' => 'ESP32-SM-001',
            'name' => 'Soil Moisture Probe A1',
            'type' => 'soil_moisture',
            'status' => 'online',
            'model' => 'ESP32-WROOM',
            'firmware_version' => 'v2.4.1',
            'battery_level' => 82.5,
            'last_reading_at' => now()->subMinutes(3),
        ]);

        $sensor2 = Sensor::create([
            'farm_id' => $farm1->id,
            'field_id' => $field1->id,
            'device_id' => 'ESP32-TH-002',
            'name' => 'Temp/Humidity Station A1',
            'type' => 'multi',
            'status' => 'online',
            'model' => 'ESP32-DHT22',
            'firmware_version' => 'v2.4.1',
            'battery_level' => 94.0,
            'last_reading_at' => now()->subMinutes(1),
        ]);

        $sensor3 = Sensor::create([
            'farm_id' => $farm1->id,
            'field_id' => $field2->id,
            'device_id' => 'ESP32-SM-003',
            'name' => 'Soil Moisture Probe B2',
            'type' => 'soil_moisture',
            'status' => 'online',
            'model' => 'ESP32-WROOM',
            'firmware_version' => 'v2.3.8',
            'battery_level' => 67.0,
            'last_reading_at' => now()->subMinutes(5),
        ]);

        $sensor4 = Sensor::create([
            'farm_id' => $farm1->id,
            'field_id' => $field3->id,
            'device_id' => 'ESP32-WL-004',
            'name' => 'Water Level Tank 1',
            'type' => 'water_level',
            'status' => 'online',
            'model' => 'ESP32-ULTRA',
            'firmware_version' => 'v2.4.0',
            'battery_level' => 55.0,
            'last_reading_at' => now()->subMinutes(2),
        ]);

        $sensor5 = Sensor::create([
            'farm_id' => $farm1->id,
            'field_id' => null,
            'device_id' => 'ESP32-LI-005',
            'name' => 'Light Intensity Monitor',
            'type' => 'light_intensity',
            'status' => 'offline',
            'model' => 'ESP32-BH1750',
            'firmware_version' => 'v2.2.0',
            'battery_level' => 12.0,
            'last_reading_at' => now()->subHours(6),
        ]);

        $sensor6 = Sensor::create([
            'farm_id' => $farm2->id,
            'field_id' => $field4->id,
            'device_id' => 'ESP32-PH-006',
            'name' => 'pH Sensor Alpha',
            'type' => 'ph',
            'status' => 'online',
            'model' => 'ESP32-PH4502',
            'firmware_version' => 'v2.4.1',
            'battery_level' => 78.0,
            'last_reading_at' => now()->subMinutes(4),
        ]);

        // Generate sensor readings (last 24 hours, every 30 min)
        $now = Carbon::now();
        for ($i = 48; $i >= 0; $i--) {
            $time = $now->copy()->subMinutes($i * 30);

            SensorReading::create([
                'sensor_id' => $sensor1->id,
                'soil_moisture' => 38 + rand(-8, 12),
                'recorded_at' => $time,
            ]);

            SensorReading::create([
                'sensor_id' => $sensor2->id,
                'temperature' => 28 + sin($i * 0.3) * 8 + rand(-2, 2),
                'humidity' => 55 + cos($i * 0.2) * 15 + rand(-3, 3),
                'recorded_at' => $time,
            ]);

            SensorReading::create([
                'sensor_id' => $sensor3->id,
                'soil_moisture' => 42 + rand(-6, 8),
                'recorded_at' => $time,
            ]);

            SensorReading::create([
                'sensor_id' => $sensor4->id,
                'water_level' => max(20, 68 - ($i * 0.5) + rand(-3, 3)),
                'recorded_at' => $time,
            ]);

            if ($i % 2 === 0) {
                SensorReading::create([
                    'sensor_id' => $sensor6->id,
                    'ph_level' => 6.5 + rand(-5, 5) / 10,
                    'recorded_at' => $time,
                ]);
            }
        }

        // Irrigation logs
        IrrigationLog::create([
            'farm_id' => $farm1->id,
            'field_id' => $field1->id,
            'type' => 'automatic',
            'status' => 'active',
            'started_at' => now()->subMinutes(12),
            'duration_minutes' => null,
            'water_used_liters' => null,
            'trigger_reason' => 'Soil moisture dropped below 35% threshold',
        ]);

        IrrigationLog::create([
            'farm_id' => $farm1->id,
            'field_id' => $field2->id,
            'type' => 'scheduled',
            'status' => 'completed',
            'started_at' => now()->subHours(4),
            'ended_at' => now()->subHours(4)->addMinutes(45),
            'duration_minutes' => 45,
            'water_used_liters' => 2250.00,
            'trigger_reason' => 'Morning schedule',
        ]);

        IrrigationLog::create([
            'farm_id' => $farm1->id,
            'field_id' => $field1->id,
            'type' => 'manual',
            'status' => 'completed',
            'started_at' => now()->subDay()->subHours(2),
            'ended_at' => now()->subDay()->subHour(),
            'duration_minutes' => 60,
            'water_used_liters' => 3100.00,
            'trigger_reason' => 'Manual override by Gurpreet',
        ]);

        for ($i = 2; $i <= 7; $i++) {
            IrrigationLog::create([
                'farm_id' => $farm1->id,
                'field_id' => collect([$field1->id, $field2->id, $field3->id])->random(),
                'type' => collect(['automatic', 'scheduled', 'manual'])->random(),
                'status' => 'completed',
                'started_at' => now()->subDays($i)->setHour(rand(5, 18)),
                'ended_at' => now()->subDays($i)->setHour(rand(5, 18))->addMinutes(rand(20, 90)),
                'duration_minutes' => rand(20, 90),
                'water_used_liters' => rand(800, 4000),
                'trigger_reason' => collect([
                    'Scheduled morning irrigation',
                    'Soil moisture below threshold',
                    'Manual override',
                    'Temperature-based adjustment',
                ])->random(),
            ]);
        }

        // Irrigation schedules
        IrrigationSchedule::create([
            'farm_id' => $farm1->id,
            'field_id' => $field1->id,
            'name' => 'Morning Drip - North Block',
            'start_time' => '06:00',
            'duration_minutes' => 45,
            'days_of_week' => ['mon', 'wed', 'fri'],
            'moisture_threshold' => 35.00,
            'is_active' => true,
            'is_smart' => true,
        ]);

        IrrigationSchedule::create([
            'farm_id' => $farm1->id,
            'field_id' => $field2->id,
            'name' => 'Evening Sprinkler - South Block',
            'start_time' => '17:30',
            'duration_minutes' => 30,
            'days_of_week' => ['tue', 'thu', 'sat'],
            'moisture_threshold' => 40.00,
            'is_active' => true,
            'is_smart' => false,
        ]);

        // Alerts
        Alert::create([
            'farm_id' => $farm1->id,
            'sensor_id' => $sensor1->id,
            'title' => 'Low Soil Moisture Detected',
            'message' => 'Soil moisture in North Block A1 dropped to 32%. Automatic irrigation triggered.',
            'type' => 'warning',
            'category' => 'moisture',
            'is_read' => false,
            'created_at' => now()->subMinutes(12),
        ]);

        Alert::create([
            'farm_id' => $farm1->id,
            'sensor_id' => $sensor5->id,
            'title' => 'Sensor Offline — Battery Critical',
            'message' => 'Light Intensity Monitor (ESP32-LI-005) battery at 12%. Device went offline 6 hours ago.',
            'type' => 'critical',
            'category' => 'device',
            'is_read' => false,
            'created_at' => now()->subHours(6),
        ]);

        Alert::create([
            'farm_id' => $farm1->id,
            'sensor_id' => $sensor4->id,
            'title' => 'Water Tank Level Below 50%',
            'message' => 'Tank 1 water level at 45%. Consider refilling before next scheduled irrigation.',
            'type' => 'warning',
            'category' => 'water_level',
            'is_read' => true,
            'created_at' => now()->subHours(3),
        ]);

        Alert::create([
            'farm_id' => $farm1->id,
            'sensor_id' => $sensor2->id,
            'title' => 'High Temperature Alert',
            'message' => 'Temperature at Station A1 reached 38°C. Crop stress risk elevated.',
            'type' => 'warning',
            'category' => 'temperature',
            'is_read' => true,
            'is_resolved' => true,
            'resolved_at' => now()->subHour(),
            'created_at' => now()->subHours(5),
        ]);

        Alert::create([
            'farm_id' => $farm1->id,
            'title' => 'Irrigation Completed Successfully',
            'message' => 'Morning scheduled irrigation for South Block B2 completed. 2,250L used over 45 mins.',
            'type' => 'success',
            'category' => 'irrigation',
            'is_read' => true,
            'created_at' => now()->subHours(4),
        ]);

        Alert::create([
            'farm_id' => $farm1->id,
            'title' => 'Rain Expected Tomorrow',
            'message' => 'Weather forecast shows 60% chance of rain tomorrow. Consider pausing scheduled irrigation.',
            'type' => 'info',
            'category' => 'weather',
            'is_read' => false,
            'created_at' => now()->subHours(1),
        ]);

        // Disease reports
        DiseaseReport::create([
            'farm_id' => $farm1->id,
            'crop_id' => $crop3->id,
            'image_path' => 'diseases/mustard_aphid_sample.jpg',
            'disease_name' => 'Mustard Aphid Infestation',
            'confidence' => 89.5,
            'severity' => 'medium',
            'description' => 'Aphid clusters detected on underside of leaves in East Block C1. Colony size suggests early-to-mid stage infestation.',
            'treatment' => 'Apply Imidacloprid 17.8 SL at 0.3ml/L. Spray during early morning or late evening for best results. Repeat after 10 days if infestation persists.',
            'prevention' => 'Use yellow sticky traps for early detection. Maintain field hygiene. Consider companion planting with coriander as natural repellent.',
            'status' => 'analyzed',
        ]);

        DiseaseReport::create([
            'farm_id' => $farm1->id,
            'crop_id' => $crop1->id,
            'image_path' => 'diseases/wheat_rust_sample.jpg',
            'disease_name' => 'Yellow Rust (Stripe Rust)',
            'confidence' => 94.2,
            'severity' => 'low',
            'description' => 'Early signs of stripe rust observed on lower leaves. Currently confined to a small section of North Block A1.',
            'treatment' => 'Apply Propiconazole 25EC at 0.1% concentration. Ensure thorough coverage of foliage. Monitor adjacent areas.',
            'prevention' => 'Use resistant varieties for next season. Ensure proper spacing between plants. Avoid excessive nitrogen fertilization.',
            'status' => 'treated',
        ]);

        // Weather data (last 7 days)
        $conditions = ['Sunny', 'Partly Cloudy', 'Cloudy', 'Light Rain', 'Clear', 'Haze', 'Windy'];
        $icons = ['sun', 'cloud-sun', 'cloud', 'cloud-rain', 'moon', 'smog', 'wind'];

        for ($i = 7; $i >= 0; $i--) {
            WeatherData::create([
                'farm_id' => $farm1->id,
                'temperature' => 28 + rand(-5, 10),
                'humidity' => 50 + rand(-15, 20),
                'wind_speed' => 8 + rand(-4, 12),
                'wind_direction' => collect(['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'])->random(),
                'rainfall_mm' => $i === 3 ? 12.5 : ($i === 5 ? 3.2 : 0),
                'condition' => $conditions[array_rand($conditions)],
                'icon' => $icons[array_rand($icons)],
                'uv_index' => 5 + rand(-2, 4),
                'visibility_km' => rand(5, 15),
                'pressure_hpa' => 1010 + rand(-8, 8),
                'recorded_at' => now()->subDays($i)->setHour(12),
            ]);
        }

        // Activity logs
        $activities = [
            ['action' => 'irrigation.started', 'description' => 'Automatic irrigation started for North Block A1', 'created_at' => now()->subMinutes(12)],
            ['action' => 'alert.created', 'description' => 'Low moisture alert triggered for ESP32-SM-001', 'created_at' => now()->subMinutes(12)],
            ['action' => 'sensor.reading', 'description' => 'All sensors reported readings successfully', 'created_at' => now()->subMinutes(30)],
            ['action' => 'weather.updated', 'description' => 'Weather data updated from API', 'created_at' => now()->subHour()],
            ['action' => 'disease.detected', 'description' => 'Crop disease analysis completed for East Block C1', 'created_at' => now()->subHours(2)],
            ['action' => 'irrigation.completed', 'description' => 'Scheduled irrigation completed for South Block B2. Used 2,250L', 'created_at' => now()->subHours(4)],
            ['action' => 'alert.resolved', 'description' => 'High temperature alert auto-resolved — temperature normalized', 'created_at' => now()->subHour()],
            ['action' => 'sensor.offline', 'description' => 'Light Intensity Monitor went offline — low battery', 'created_at' => now()->subHours(6)],
            ['action' => 'user.login', 'description' => 'Gurpreet Singh logged in from mobile app', 'created_at' => now()->subHours(2)],
            ['action' => 'farm.updated', 'description' => 'Farm settings updated for Punjab Wheat Farm', 'created_at' => now()->subDay()],
        ];

        foreach ($activities as $activity) {
            ActivityLog::create([
                'user_id' => $farmer->id,
                'farm_id' => $farm1->id,
                'action' => $activity['action'],
                'description' => $activity['description'],
                'created_at' => $activity['created_at'],
                'updated_at' => $activity['created_at'],
            ]);
        }
    }
}
