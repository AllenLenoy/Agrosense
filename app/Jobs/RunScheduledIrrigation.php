<?php

namespace App\Jobs;

use App\Models\IrrigationSchedule;
use App\Models\IrrigationLog;
use App\Models\Sensor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunScheduledIrrigation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // ── Auto-stop overdue active irrigations ─────────────────────────────
        $activeLogs = IrrigationLog::where('status', 'active')
            ->whereNotNull('duration_minutes')
            ->get();

        foreach ($activeLogs as $log) {
            $elapsedMinutes = (int) abs(now()->diffInMinutes($log->started_at));
            if ($elapsedMinutes >= $log->duration_minutes) {
                $log->update([
                    'status' => 'completed',
                    'ended_at' => now(),
                    'water_used_liters' => $log->duration_minutes * 500,
                ]);
                try {
                    \App\Events\IrrigationStatusChanged::dispatch($log);
                } catch (\Exception $e) {
                    // Fail gracefully
                }
            }
        }

        // ── Start new scheduled irrigations ──────────────────────────────────
        $now = now();
        $currentTime = $now->format('H:i');
        $currentDay = strtolower($now->format('l')); // 'monday', 'tuesday', etc.

        // Get all active schedules
        $schedules = IrrigationSchedule::where('is_active', true)->get();

        foreach ($schedules as $schedule) {
            // Check if schedule should run today
            if (!in_array($currentDay, $schedule->days_of_week)) {
                continue;
            }

            // Check if schedule start time is now
            // To handle exactly the minute, we ensure it matches H:i
            $scheduleTime = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
            if ($currentTime !== $scheduleTime) {
                continue;
            }

            // If it's a smart schedule, check moisture threshold
            if ($schedule->is_smart) {
                // Find latest moisture reading for the field
                // Assuming we get the latest reading from any sensor in the field
                $sensors = Sensor::where('field_id', $schedule->field_id)->whereIn('type', ['soil_moisture', 'multi'])->get();
                $shouldIrrigate = false;
                
                foreach ($sensors as $sensor) {
                    $reading = $sensor->latestReading;
                    if ($reading && $reading->soil_moisture < $schedule->moisture_threshold) {
                        $shouldIrrigate = true;
                        break;
                    }
                }

                // If no sensors are found, we might still irrigate, or maybe skip?
                // Let's assume we skip if it's smart and we don't have data, or we could force it.
                // It's safer to skip or irrigate? The prompt implies "checks moisture threshold if is_smart".
                if ($sensors->count() > 0 && !$shouldIrrigate) {
                    continue; // Moisture is high enough, skip irrigation
                }
            }

            // Start irrigation
            $this->startIrrigation($schedule);
        }
    }

    private function startIrrigation(IrrigationSchedule $schedule)
    {
        // Don't start if already active
        $activeCount = IrrigationLog::where('field_id', $schedule->field_id)
            ->where('status', 'active')
            ->count();

        if ($activeCount > 0) {
            return;
        }

        $log = IrrigationLog::create([
            'farm_id' => $schedule->farm_id,
            'field_id' => $schedule->field_id,
            'type' => 'scheduled',
            'status' => 'active',
            'started_at' => now(),
            'trigger_reason' => 'Scheduled by ' . $schedule->name,
            'duration_minutes' => $schedule->duration_minutes, // Although we might end it dynamically, we can store it here or leave it. Actually the controller calculates it on end.
        ]);

        try {
            \App\Events\IrrigationStatusChanged::dispatch($log);
        } catch (\Exception $e) {
            // Fail gracefully
        }
    }
}
