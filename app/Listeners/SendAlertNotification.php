<?php

namespace App\Listeners;

use App\Events\AlertCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendAlertNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AlertCreated $event): void
    {
        $alert = $event->alert;
        $farm = $alert->farm;

        // Find the farmer who owns the farm
        $farmer = $farm->user;
        
        if ($farmer) {
            $farmer->notify(new \App\Notifications\FarmAlertNotification($alert));
        }

        // If alert is high or critical priority, notify admins as well
        if (in_array($alert->priority, ['high', 'critical'])) {
            $admins = \App\Models\User::role('Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new \App\Notifications\FarmAlertNotification($alert));
            }
        }
    }
}
