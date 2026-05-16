<?php

namespace App\Http\Controllers;

use App\Models\WeatherData;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeatherController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $farms = $user->isAdmin()
            ? Farm::all()
            : $user->farms;

        $activeFarm = $farms->first();
        $currentWeather = null;
        $forecast = collect();

        if ($activeFarm) {
            $currentWeather = WeatherData::where('farm_id', $activeFarm->id)
                ->latest('recorded_at')
                ->first();

            $forecast = WeatherData::where('farm_id', $activeFarm->id)
                ->orderBy('recorded_at', 'desc')
                ->take(7)
                ->get()
                ->reverse()
                ->values();
        }

        return view('weather.index', compact('farms', 'activeFarm', 'currentWeather', 'forecast'));
    }
}
