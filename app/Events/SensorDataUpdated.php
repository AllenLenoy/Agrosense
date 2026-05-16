<?php

namespace App\Events;

use App\Models\SensorReading;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SensorDataUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reading;

    public function __construct(SensorReading $reading)
    {
        $this->reading = $reading->load('sensor.farm');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('farm.' . $this->reading->sensor->farm_id),
        ];
    }
    
    public function broadcastWith(): array
    {
        return [
            'sensor_id' => $this->reading->sensor_id,
            'farm_id' => $this->reading->sensor->farm_id,
            'soil_moisture' => $this->reading->soil_moisture,
            'temperature' => $this->reading->temperature,
            'humidity' => $this->reading->humidity,
            'recorded_at' => $this->reading->recorded_at->format('H:i'),
        ];
    }
}
