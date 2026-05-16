<?php

namespace App\Jobs;

use App\Models\Sensor;
use App\Models\SensorReading;
use App\Models\Alert;
use App\Events\SensorDataUpdated;
use App\Events\AlertCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SimulateSensorReadings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $sensors = Sensor::where('status', 'online')->get();

        foreach ($sensors as $sensor) {
            $lastReading = $sensor->latestReading;
            
            $newReading = new SensorReading([
                'sensor_id' => $sensor->id,
                'recorded_at' => now(),
            ]);

            // Simulate slight variations based on sensor type
            if ($sensor->type === 'soil_moisture' || $sensor->type === 'multi') {
                $baseMoisture = $lastReading ? $lastReading->soil_moisture : 45;
                // Slowly decrease moisture, or jump up if irrigation is active
                $newReading->soil_moisture = max(10, min(100, $baseMoisture + rand(-2, 1)));
                
                // Trigger alert if moisture is too low
                if ($newReading->soil_moisture < 30 && rand(1, 10) > 8) {
                    $alert = Alert::create([
                        'farm_id' => $sensor->farm_id,
                        'sensor_id' => $sensor->id,
                        'title' => 'Critical Soil Moisture',
                        'message' => "Moisture dropped to {$newReading->soil_moisture}% at {$sensor->name}",
                        'type' => 'critical',
                        'category' => 'moisture',
                    ]);
                    broadcast(new AlertCreated($alert));
                }
            }

            if ($sensor->type === 'multi') {
                $baseTemp = $lastReading ? $lastReading->temperature : 25;
                $newReading->temperature = max(10, min(50, $baseTemp + (rand(-10, 10) / 10)));
                
                $baseHum = $lastReading ? $lastReading->humidity : 60;
                $newReading->humidity = max(20, min(90, $baseHum + rand(-2, 2)));
            }

            $newReading->save();
            $sensor->update(['last_reading_at' => now()]);

            // Broadcast the new reading
            broadcast(new SensorDataUpdated($newReading));
        }
    }
}
