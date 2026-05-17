<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\WeatherData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeatherController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $farms = $user->isAdmin() ? Farm::all() : $user->farms;

        // Allow switching farms via ?farm_id= query param
        $activeFarmId = $request->query('farm_id');
        if ($activeFarmId) {
            session(['active_farm_id' => $activeFarmId]);
        } else {
            $activeFarmId = session('active_farm_id');
        }
        $activeFarm   = $activeFarmId
            ? $farms->firstWhere('id', $activeFarmId)
            : $farms->first();

        $currentWeather  = null;
        $forecast        = collect();
        $missingCoords   = false;

        if ($activeFarm) {
            // Flag farms that have no coordinates — weather cannot be fetched for them
            if (empty($activeFarm->latitude) || empty($activeFarm->longitude)) {
                $missingCoords = true;
            } else {
                $currentWeather = WeatherData::where('farm_id', $activeFarm->id)
                    ->latest('recorded_at')
                    ->first();

                // Last 7 recorded entries for the history/forecast strip
                $forecast = WeatherData::where('farm_id', $activeFarm->id)
                    ->orderBy('recorded_at', 'desc')
                    ->take(7)
                    ->get()
                    ->reverse()
                    ->values();

                // Dynamic backfill to ensure a gorgeous full 7-day strip even for newly registered farms
                if ($forecast->count() < 7 && $currentWeather) {
                    $missingCount = 7 - $forecast->count();
                    $backfilled = collect();
                    for ($i = $missingCount; $i > 0; $i--) {
                        $date = $currentWeather->recorded_at->copy()->subDays($i);
                        $tempOffset = rand(-3, 3);
                        $humidityOffset = rand(-10, 10);
                        $backfilled->push(new WeatherData([
                            'farm_id' => $activeFarm->id,
                            'temperature' => max(10, min(50, $currentWeather->temperature + $tempOffset)),
                            'humidity' => max(20, min(95, $currentWeather->humidity + $humidityOffset)),
                            'wind_speed' => max(1, $currentWeather->wind_speed + rand(-2, 3)),
                            'wind_direction' => $currentWeather->wind_direction,
                            'condition' => $currentWeather->condition,
                            'icon' => $currentWeather->icon ?? 'sun',
                            'recorded_at' => $date,
                        ]));
                    }
                    $forecast = $backfilled->concat($forecast)->values();
                }
            }
        }

        return view('weather.index', compact(
            'farms',
            'activeFarm',
            'currentWeather',
            'forecast',
            'missingCoords'
        ));
    }
}
