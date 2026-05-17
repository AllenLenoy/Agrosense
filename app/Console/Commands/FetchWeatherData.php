<?php

namespace App\Console\Commands;

use App\Models\Farm;
use App\Models\WeatherData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchWeatherData extends Command
{
    /**
     * Usage:
     *   php artisan weather:fetch          — fetches for all active farms
     *   php artisan weather:fetch --farm=5 — fetches for a single farm by ID
     */
    protected $signature = 'weather:fetch {--farm= : Fetch weather for a specific farm ID only}';

    protected $description = 'Fetch current weather data from OpenWeatherMap and store it for each farm';

    public function handle(): int
    {
        $apiKey = config('services.openweathermap.key');

        if (empty($apiKey)) {
            $this->error('OPENWEATHERMAP_API_KEY is not set in your .env file.');
            $this->line('  Get a free key at https://openweathermap.org/api');
            return Command::FAILURE;
        }

        // Resolve which farms to process
        $farmId = $this->option('farm');
        $farms  = $farmId
            ? Farm::where('id', $farmId)->where('is_active', true)->get()
            : Farm::where('is_active', true)->get();

        if ($farms->isEmpty()) {
            $this->warn('No active farms found to fetch weather for.');
            return Command::SUCCESS;
        }

        $success = 0;
        $failed  = 0;

        foreach ($farms as $farm) {
            // A farm needs coordinates to fetch weather
            if (empty($farm->latitude) || empty($farm->longitude)) {
                $this->warn("  [{$farm->name}] Skipped — no latitude/longitude set.");
                continue;
            }

            $this->line("  Fetching weather for: {$farm->name} ({$farm->latitude}, {$farm->longitude})");

            try {
                $response = Http::timeout(10)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat'   => $farm->latitude,
                    'lon'   => $farm->longitude,
                    'appid' => $apiKey,
                    'units' => 'metric', // Celsius
                ]);

                if ($response->failed()) {
                    $this->error("  [{$farm->name}] API error: HTTP {$response->status()}");
                    $failed++;
                    continue;
                }

                $data = $response->json();

                // Map OpenWeatherMap icon codes to Font Awesome icon names
                $iconMap = [
                    '01' => 'sun',
                    '02' => 'cloud-sun',
                    '03' => 'cloud',
                    '04' => 'cloud',
                    '09' => 'cloud-showers-heavy',
                    '10' => 'cloud-rain',
                    '11' => 'bolt',
                    '13' => 'snowflake',
                    '50' => 'smog',
                ];

                $owmIcon = substr($data['weather'][0]['icon'] ?? '01d', 0, 2);
                $faIcon  = $iconMap[$owmIcon] ?? 'cloud';

                WeatherData::create([
                    'farm_id'        => $farm->id,
                    'temperature'    => $data['main']['temp']     ?? null,
                    'humidity'       => $data['main']['humidity'] ?? null,
                    'wind_speed'     => isset($data['wind']['speed'])
                                            ? round($data['wind']['speed'] * 3.6, 2) // m/s → km/h
                                            : null,
                    'wind_direction' => $this->degreesToCompass($data['wind']['deg'] ?? null),
                    'rainfall_mm'    => $data['rain']['1h'] ?? 0,
                    'condition'      => ucfirst($data['weather'][0]['description'] ?? 'Unknown'),
                    'icon'           => $faIcon,
                    'uv_index'       => null, // Requires a separate One Call API endpoint
                    'visibility_km'  => isset($data['visibility'])
                                            ? round($data['visibility'] / 1000, 1)
                                            : null,
                    'pressure_hpa'   => $data['main']['pressure'] ?? null,
                    'recorded_at'    => now(),
                ]);

                $this->info("  [{$farm->name}] Saved — {$data['weather'][0]['description']}, {$data['main']['temp']}°C");
                $success++;

            } catch (\Exception $e) {
                $this->error("  [{$farm->name}] Exception: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Success: {$success} | Failed/Skipped: {$failed}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * Convert wind degrees to a compass direction string.
     */
    private function degreesToCompass(?float $degrees): string
    {
        if ($degrees === null) {
            return 'N/A';
        }

        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
                       'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];

        $index = (int) round($degrees / 22.5) % 16;

        return $directions[$index];
    }
}
