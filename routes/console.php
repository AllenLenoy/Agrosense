<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Jobs\SimulateSensorReadings;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SimulateSensorReadings)->everyMinute();
Schedule::job(new \App\Jobs\RunScheduledIrrigation)->everyMinute();

// Fetch live weather from OpenWeatherMap every 30 minutes for all active farms.
// Requires OPENWEATHERMAP_API_KEY to be set in .env
Schedule::command('weather:fetch')->everyThirtyMinutes();
