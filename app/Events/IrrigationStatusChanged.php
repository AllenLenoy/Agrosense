<?php

namespace App\Events;

use App\Models\IrrigationLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IrrigationStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $log;

    public function __construct(IrrigationLog $log)
    {
        $this->log = $log;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('farm.' . $this->log->farm_id),
        ];
    }
    
    public function broadcastWith(): array
    {
        return [
            'id' => $this->log->id,
            'farm_id' => $this->log->farm_id,
            'field_id' => $this->log->field_id,
            'status' => $this->log->status, // 'active' or 'completed'
        ];
    }
}
