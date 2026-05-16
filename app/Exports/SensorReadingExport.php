<?php

namespace App\Exports;

use App\Models\SensorReading;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SensorReadingExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $farmId;
    protected $startDate;
    protected $endDate;

    public function __construct(int $farmId, string $startDate, string $endDate)
    {
        $this->farmId = $farmId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return SensorReading::query()
            ->join('sensors', 'sensor_readings.sensor_id', '=', 'sensors.id')
            ->where('sensors.farm_id', $this->farmId)
            ->whereBetween('sensor_readings.recorded_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->select('sensor_readings.*', 'sensors.name as sensor_name', 'sensors.device_id')
            ->orderBy('sensor_readings.recorded_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Time',
            'Device Name',
            'Device ID',
            'Soil Moisture (%)',
            'Temperature (°C)',
            'Humidity (%)',
            'Water Level (%)',
            'Light Intensity (lux)',
            'pH Level'
        ];
    }

    public function map($reading): array
    {
        return [
            $reading->recorded_at ? $reading->recorded_at->format('Y-m-d H:i:s') : '-',
            $reading->sensor_name,
            $reading->device_id,
            $reading->soil_moisture ?? '-',
            $reading->temperature ?? '-',
            $reading->humidity ?? '-',
            $reading->water_level ?? '-',
            $reading->light_intensity ?? '-',
            $reading->ph_level ?? '-'
        ];
    }
}
