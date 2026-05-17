<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Farm;
use App\Models\Sensor;
use App\Models\Alert;
use App\Models\IrrigationLog;
use App\Models\SensorReading;
use App\Models\ActivityLog;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $weekAgo = $now->copy()->subDays(7);
        $monthAgo = $now->copy()->subDays(30);

        // Core stats
        $stats = [
            'total_users'      => User::count(),
            'total_farms'      => Farm::count(),
            'total_sensors'    => Sensor::count(),
            'active_sensors'   => Sensor::where('status', 'online')->count(),
            'offline_sensors'  => Sensor::where('status', '!=', 'online')->count(),
            'critical_alerts'  => Alert::where('type', 'critical')->where('is_resolved', false)->count(),
            'unresolved_alerts'=> Alert::where('is_resolved', false)->count(),
            'total_alerts'     => Alert::count(),
            'new_users_week'   => User::where('created_at', '>=', $weekAgo)->count(),
            'new_farms_week'   => Farm::where('created_at', '>=', $weekAgo)->count(),
            'total_area'       => Farm::sum('area_acres'),
            'irrigation_today' => IrrigationLog::whereDate('started_at', today())->count(),
            'water_today'      => IrrigationLog::whereDate('started_at', today())->sum('water_used_liters'),
        ];

        // Sensor health breakdown
        $sensorHealth = [
            'online'      => Sensor::where('status', 'online')->count(),
            'offline'     => Sensor::where('status', 'offline')->count(),
            'maintenance' => Sensor::where('status', 'maintenance')->count(),
            'low_battery' => Sensor::where('battery_level', '<', 20)->count(),
        ];

        // User registration trend (last 7 days)
        $userTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            $userTrend[] = [
                'label' => $date->format('M d'),
                'count' => User::whereDate('created_at', $date->toDateString())->count(),
            ];
        }

        // Alert distribution by type
        $alertsByType = Alert::selectRaw("type, COUNT(*) as count")
            ->where('is_resolved', false)
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Recent users
        $recentUsers = User::latest()->take(5)->get();

        // Recent farms
        $farms = Farm::with('owner')->withCount('sensors')->latest()->take(5)->get();

        // Recent alerts (unresolved)
        $recentAlerts = Alert::with(['farm', 'sensor'])->where('is_resolved', false)
            ->latest()->take(5)->get();

        // Recent activity
        $recentActivity = ActivityLog::with('user')->latest()->take(8)->get();

        return view('admin.index', compact(
            'stats', 'sensorHealth', 'userTrend', 'alertsByType',
            'recentUsers', 'farms', 'recentAlerts', 'recentActivity'
        ));
    }

    public function users(Request $request)
    {
        $query = User::withCount('farms')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(12);
        $totalUsers = User::count();
        $adminCount = User::where('role', 'admin')->count();
        $farmerCount = User::where('role', 'farmer')->count();

        return view('admin.users', compact('users', 'totalUsers', 'adminCount', 'farmerCount'));
    }

    public function devices(Request $request)
    {
        $query = Sensor::with(['farm', 'field'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $sensors = $query->paginate(12);

        $deviceStats = [
            'total'       => Sensor::count(),
            'online'      => Sensor::where('status', 'online')->count(),
            'offline'     => Sensor::where('status', 'offline')->count(),
            'maintenance' => Sensor::where('status', 'maintenance')->count(),
            'low_battery' => Sensor::where('battery_level', '<', 20)->count(),
        ];

        $sensorTypes = Sensor::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')->pluck('count', 'type')->toArray();

        return view('admin.devices', compact('sensors', 'deviceStats', 'sensorTypes'));
    }

    public function storeDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|unique:sensors,device_id',
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'farm_id' => 'nullable|exists:farms,id',
        ]);

        Sensor::create([
            'device_id' => $request->device_id,
            'name' => $request->name,
            'type' => $request->type,
            'farm_id' => $request->farm_id,
            'status' => 'offline',
            'battery_level' => 100,
        ]);

        return back()->with('success', 'Device registered successfully.');
    }

    public function updateDevice(Request $request, Sensor $sensor)
    {
        $request->validate([
            'device_id' => 'required|string|unique:sensors,device_id,' . $sensor->id,
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'farm_id' => 'nullable|exists:farms,id',
            'status' => 'required|in:online,offline,maintenance',
        ]);

        $sensor->update([
            'device_id' => $request->device_id,
            'name' => $request->name,
            'type' => $request->type,
            'farm_id' => $request->farm_id,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Device updated successfully.');
    }

    public function deleteDevice(Sensor $sensor)
    {
        $sensor->delete();
        return back()->with('success', 'Device removed successfully.');
    }

    public function alerts(Request $request)
    {
        $query = Alert::with(['farm', 'sensor'])->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'resolved') {
                $query->where('is_resolved', true);
            } else {
                $query->where('is_resolved', false);
            }
        }

        $alerts = $query->paginate(15);

        $alertStats = [
            'total'      => Alert::count(),
            'unresolved' => Alert::where('is_resolved', false)->count(),
            'critical'   => Alert::where('type', 'critical')->where('is_resolved', false)->count(),
            'warning'    => Alert::where('type', 'warning')->where('is_resolved', false)->count(),
            'info'       => Alert::where('type', 'info')->where('is_resolved', false)->count(),
        ];

        return view('admin.alerts', compact('alerts', 'alertStats'));
    }

    public function activity()
    {
        $logs = ActivityLog::with('user')->latest()->paginate(20);
        return view('admin.activity', compact('logs'));
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,farmer',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,farmer',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->password)]);
        }

        return back()->with('success', 'User updated successfully.');
    }

    public function toggleUser(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);
        return back()->with('success', "User {$user->name} has been " . ($user->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();
        return back()->with('success', "User {$user->name} has been deleted.");
    }
}
