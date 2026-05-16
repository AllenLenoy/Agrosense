<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\Sensor;
use App\Models\SensorReading;
use App\Events\SensorDataUpdated;

class ListenForMqttReadings extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT broker for incoming sensor readings';

    public function handle()
    {
        // Defaulting to a free public broker for demo purposes.
        $server   = env('MQTT_HOST', 'broker.emqx.io');
        $port     = env('MQTT_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'agrosense_server_' . uniqid());

        try {
            $mqtt = new MqttClient($server, $port, $clientId);

            $connectionSettings = (new ConnectionSettings)
                ->setKeepAliveInterval(60)
                ->setUseTls(false); 
            
            $mqtt->connect($connectionSettings, true);

            $this->info("Connected to MQTT Broker at {$server}:{$port}");
            $this->info("Listening for incoming data on agrosense/sensors/#");

            $mqtt->subscribe('agrosense/sensors/+', function ($topic, $message) {
                $this->info(sprintf("Received message on topic [%s]: %s", $topic, $message));
                
                // Expected Topic: agrosense/sensors/{device_id}
                $topicParts = explode('/', $topic);
                $deviceId = end($topicParts);

                $payload = json_decode($message, true);

                if ($payload) {
                    $sensor = Sensor::where('device_id', $deviceId)->first();
                    
                    if ($sensor) {
                        // Update sensor status and heartbeat
                        $sensor->update([
                            'status' => 'online',
                            'battery_level' => $payload['battery'] ?? $sensor->battery_level,
                            'last_reading_at' => now()
                        ]);

                        // Save the new reading to the database
                        $reading = SensorReading::create([
                            'sensor_id' => $sensor->id,
                            'soil_moisture' => $payload['soil_moisture'] ?? null,
                            'temperature' => $payload['temperature'] ?? null,
                            'humidity' => $payload['humidity'] ?? null,
                            'water_level' => $payload['water_level'] ?? null,
                            'recorded_at' => now()
                        ]);

                        // Broadcast to UI via WebSockets (Module 4)
                        event(new SensorDataUpdated($reading));

                        // Check Thresholds and Create Alerts
                        if (isset($payload['soil_moisture']) && $payload['soil_moisture'] < 30) {
                            $alert = \App\Models\Alert::create([
                                'farm_id' => $sensor->farm_id,
                                'sensor_id' => $sensor->id,
                                'type' => 'moisture_low',
                                'priority' => 'high',
                                'title' => 'Low Soil Moisture Detected',
                                'message' => "Sensor {$sensor->name} reported a critical moisture level of {$payload['soil_moisture']}%.",
                            ]);
                            event(new \App\Events\AlertCreated($alert));
                        }

                        if (isset($payload['temperature']) && $payload['temperature'] > 35) {
                            $alert = \App\Models\Alert::create([
                                'farm_id' => $sensor->farm_id,
                                'sensor_id' => $sensor->id,
                                'type' => 'temperature_high',
                                'priority' => 'high',
                                'title' => 'High Temperature Alert',
                                'message' => "Sensor {$sensor->name} reported a high temperature of {$payload['temperature']}°C.",
                            ]);
                            event(new \App\Events\AlertCreated($alert));
                        }

                        $this->info("Processed reading for {$sensor->name}");
                    } else {
                        $this->warn("Unknown device ID: {$deviceId}");
                    }
                }
            }, 0);

            $mqtt->loop(true);
            $mqtt->disconnect();

        } catch (\Exception $e) {
            $this->error("MQTT Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
