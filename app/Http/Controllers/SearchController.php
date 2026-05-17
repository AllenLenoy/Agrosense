<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\Farm;
use App\Models\Alert;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');
        
        if (empty($query)) {
            return back()->with('error', 'Please enter a search term.');
        }

        $user = Auth::user();
        
        // Search Farms
        $farmsQuery = Farm::where(function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('location', 'like', "%{$query}%");
        });
                         
        if (!$user->isAdmin()) {
            $farmsQuery->where('user_id', $user->id);
        }
        $farms = $farmsQuery->get();
        
        $farmIds = $user->isAdmin() ? Farm::pluck('id') : $user->farms()->pluck('id');

        // Search Sensors
        $sensors = Sensor::whereIn('farm_id', $farmIds)
                        ->where(function($q) use ($query) {
                            $q->where('name', 'like', "%{$query}%")
                              ->orWhere('type', 'like', "%{$query}%")
                              ->orWhere('device_id', 'like', "%{$query}%");
                        })->get();

        // Search Alerts
        $alerts = Alert::whereIn('farm_id', $farmIds)
                      ->where(function($q) use ($query) {
                          $q->where('title', 'like', "%{$query}%")
                            ->orWhere('message', 'like', "%{$query}%");
                      })->get();

        return view('search.index', compact('query', 'farms', 'sensors', 'alerts'));
    }
}
