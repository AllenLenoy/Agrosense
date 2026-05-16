<?php

namespace App\Http\Controllers;

use App\Models\IrrigationLog;
use App\Models\IrrigationSchedule;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IrrigationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $farmIds = $user->isAdmin()
            ? Farm::pluck('id')
            : $user->farms()->pluck('id');

        $activeLogs = IrrigationLog::whereIn('farm_id', $farmIds)
            ->where('status', 'active')
            ->with(['farm', 'field'])
            ->get();

        $recentLogs = IrrigationLog::whereIn('farm_id', $farmIds)
            ->where('status', '!=', 'active')
            ->with(['farm', 'field'])
            ->latest('started_at')
            ->paginate(10);

        $schedules = IrrigationSchedule::whereIn('farm_id', $farmIds)
            ->with(['farm', 'field'])
            ->get();

        // Water usage stats
        $totalWaterToday = IrrigationLog::whereIn('farm_id', $farmIds)
            ->whereDate('started_at', today())
            ->sum('water_used_liters');

        $totalWaterWeek = IrrigationLog::whereIn('farm_id', $farmIds)
            ->where('started_at', '>=', now()->subWeek())
            ->sum('water_used_liters');

        $avgDuration = IrrigationLog::whereIn('farm_id', $farmIds)
            ->where('status', 'completed')
            ->where('started_at', '>=', now()->subWeek())
            ->avg('duration_minutes');

        $stats = [
            'active_count' => $activeLogs->count(),
            'water_today' => round($totalWaterToday),
            'water_week' => round($totalWaterWeek),
            'avg_duration' => round($avgDuration ?? 0),
            'schedule_count' => $schedules->where('is_active', true)->count(),
        ];

        return view('irrigation.index', compact('activeLogs', 'recentLogs', 'schedules', 'stats'));
    }

    public function toggle(Request $request, IrrigationLog $log)
    {
        if ($log->status === 'active') {
            $log->update([
                'status' => 'completed',
                'ended_at' => now(),
                'duration_minutes' => max(1, (int) abs(now()->diffInMinutes($log->started_at))),
            ]);

            \App\Events\IrrigationStatusChanged::dispatch($log);
        }

        return back()->with('success', 'Irrigation stopped successfully.');
    }

    public function start(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'duration' => 'required|numeric'
        ]);

        $field = \App\Models\Field::findOrFail($request->field_id);

        if (IrrigationLog::where('field_id', $field->id)->where('status', 'active')->exists()) {
            return back()->with('error', 'This field is already being irrigated.');
        }

        $log = IrrigationLog::create([
            'farm_id' => $field->farm_id,
            'field_id' => $field->id,
            'type' => 'manual',
            'status' => 'active',
            'started_at' => now(),
            'trigger_reason' => 'Manual start by ' . Auth::user()->name,
        ]);

        \App\Events\IrrigationStatusChanged::dispatch($log);

        return back()->with('success', 'Irrigation started successfully.');
    }
}
