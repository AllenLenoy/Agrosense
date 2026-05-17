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
            $durationMinutes = max(1, (int) abs(now()->diffInMinutes($log->started_at)));
            $log->update([
                'status' => 'completed',
                'ended_at' => now(),
                'duration_minutes' => $durationMinutes,
                'water_used_liters' => $durationMinutes * 500, // 500 L/min flow rate
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'farm_id' => $log->farm_id,
                'action' => 'irrigation.stop',
                'description' => Auth::user()->name . ' stopped irrigation',
                'ip_address' => $request->ip(),
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

        $field = \App\Models\Field::with('farm')->findOrFail($request->field_id);

        if (!Auth::user()->isAdmin() && $field->farm->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to this field.');
        }

        if (IrrigationLog::where('field_id', $field->id)->where('status', 'active')->exists()) {
            return back()->with('error', 'This field is already being irrigated.');
        }

        $log = IrrigationLog::create([
            'farm_id' => $field->farm_id,
            'field_id' => $field->id,
            'type' => 'manual',
            'status' => 'active',
            'started_at' => now(),
            'trigger_reason' => 'Manual start by ' . Auth::user()->name . ' (' . $request->duration . ' mins planned)',
        ]);

        \App\Models\ActivityLog::create([
            'user_id' => Auth::id(),
            'farm_id' => $field->farm_id,
            'action' => 'irrigation.start',
            'description' => Auth::user()->name . ' started irrigation on field ' . $field->name,
            'ip_address' => $request->ip(),
        ]);

        \App\Events\IrrigationStatusChanged::dispatch($log);

        return back()->with('success', 'Irrigation started successfully.');
    }

    public function storeSchedule(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'field_id' => 'nullable|exists:fields,id',
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|min:1',
            'days_of_week' => 'required|array',
            'is_smart' => 'boolean',
            'moisture_threshold' => 'nullable|integer|min:0|max:100',
        ]);

        $farm = Farm::findOrFail($request->farm_id);
        if (!Auth::user()->isAdmin() && $farm->user_id !== Auth::id()) {
            abort(403);
        }

        IrrigationSchedule::create([
            'farm_id' => $request->farm_id,
            'field_id' => $request->field_id,
            'name' => $request->name,
            'start_time' => $request->start_time,
            'duration_minutes' => $request->duration_minutes,
            'days_of_week' => $request->days_of_week,
            'moisture_threshold' => $request->moisture_threshold,
            'is_smart' => $request->has('is_smart'),
        ]);

        return back()->with('success', 'Schedule created successfully.');
    }

    public function updateSchedule(Request $request, IrrigationSchedule $schedule)
    {
        if (!Auth::user()->isAdmin() && $schedule->farm->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required',
            'duration_minutes' => 'required|integer|min:1',
            'days_of_week' => 'required|array',
            'is_smart' => 'boolean',
            'moisture_threshold' => 'nullable|integer|min:0|max:100',
        ]);

        $schedule->update([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'duration_minutes' => $request->duration_minutes,
            'days_of_week' => $request->days_of_week,
            'moisture_threshold' => $request->moisture_threshold,
            'is_smart' => $request->has('is_smart'),
            'is_active' => $request->has('is_active'),
        ]);

        return back()->with('success', 'Schedule updated successfully.');
    }

    public function destroySchedule(IrrigationSchedule $schedule)
    {
        if (!Auth::user()->isAdmin() && $schedule->farm->user_id !== Auth::id()) {
            abort(403);
        }

        $schedule->delete();

        return back()->with('success', 'Schedule deleted successfully.');
    }
}
