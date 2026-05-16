<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $farmIds = $user->isAdmin()
            ? Farm::pluck('id')
            : $user->farms()->pluck('id');

        $query = Alert::whereIn('farm_id', $farmIds)->with(['farm', 'sensor']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->boolean('unread_only')) {
            $query->unread();
        }

        $alerts = $query->latest()->paginate(15);

        $stats = [
            'total' => Alert::whereIn('farm_id', $farmIds)->count(),
            'unread' => Alert::whereIn('farm_id', $farmIds)->unread()->count(),
            'critical' => Alert::whereIn('farm_id', $farmIds)->where('type', 'critical')->unresolved()->count(),
            'warnings' => Alert::whereIn('farm_id', $farmIds)->where('type', 'warning')->unresolved()->count(),
        ];

        return view('alerts.index', compact('alerts', 'stats'));
    }

    public function markRead(Alert $alert)
    {
        $alert->update(['is_read' => true]);
        return back()->with('success', 'Alert marked as read.');
    }

    public function resolve(Alert $alert)
    {
        $alert->update([
            'is_resolved' => true,
            'is_read' => true,
            'resolved_at' => now(),
        ]);
        return back()->with('success', 'Alert resolved.');
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $farmIds = $user->isAdmin()
            ? Farm::pluck('id')
            : $user->farms()->pluck('id');

        Alert::whereIn('farm_id', $farmIds)->unread()->update(['is_read' => true]);

        return back()->with('success', 'All alerts marked as read.');
    }
}
