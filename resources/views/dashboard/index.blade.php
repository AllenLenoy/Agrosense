@extends('layouts.app')
@section('title', 'Overview')
@section('breadcrumb', 'Dashboard')

@section('content')
<style>
    .grid-metrics {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .metric-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1.25rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
    }

    .metric-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .m-moisture { background: #e0f2fe; color: #0ea5e9; }
    .m-temp { background: #ffedd5; color: #f97316; }
    .m-humidity { background: #cffafe; color: #06b6d4; }
    .m-ph { background: #dcfce7; color: #22c55e; }

    .metric-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-main);
        display: flex;
        align-items: baseline;
        gap: 0.5rem;
    }

    .metric-status {
        font-size: 0.75rem;
        font-weight: 700;
        padding: 0.2rem 0.5rem;
        border-radius: 999px;
    }

    .status-optimal { background: #dcfce7; color: #166534; }
    .status-normal { background: #dbeafe; color: #1e40af; }
    .status-warning { background: #fef3c7; color: #92400e; }
    .status-alert { background: #fee2e2; color: #b91c1c; }

    .dashboard-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
    }

    .chart-box {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .chart-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .irrigation-box {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .donut-ring {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: conic-gradient(var(--primary) 0% 60%, #e2e8f0 60% 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        margin: 1.5rem 0;
    }

    .donut-ring::after {
        content: '';
        position: absolute;
        width: 110px;
        height: 110px;
        background: #fff;
        border-radius: 50%;
    }

    .donut-content {
        position: relative;
        z-index: 1;
        display: flex;
        flex-direction: column;
    }

    .donut-val {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-main);
    }
    .donut-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
    }

    .pump-status {
        background: var(--primary);
        color: white;
        padding: 0.4rem 1rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .recent-alerts {
        margin-top: 1.5rem;
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        padding: 1.5rem;
    }

    .alert-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .alert-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .alert-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .alert-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .alert-icon {
        font-size: 1.25rem;
        margin-top: 2px;
    }

    .alert-content h4 {
        margin: 0 0 0.25rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
    }
    .alert-content p {
        margin: 0;
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .alert-time {
        font-size: 0.75rem;
        color: var(--text-muted);
        white-space: nowrap;
        margin-left: auto;
    }
</style>

<!-- Top Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Farm Overview</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Monitor real-time status of {{ $activeFarm->name ?? 'your farm' }}</p>
    </div>
    <div style="display: flex; gap: 1rem;">
        <a href="{{ route('reports.index') }}" class="btn btn-outline"><i class="fas fa-download"></i> Export</a>
        @if($activeFarm)
        <a href="{{ route('farms.show', $activeFarm) }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Sensor</a>
        @endif
    </div>
</div>

<!-- Metrics Grid -->
<div class="grid-metrics">
    <div class="metric-card">
        <div class="metric-icon m-moisture"><i class="fas fa-tint"></i></div>
        <div class="metric-label">Soil Moisture</div>
        <div class="metric-value">
            <span id="stat-moisture">{{ $stats['avg_soil_moisture'] ?? 45 }}%</span>
            <span class="metric-status status-optimal">Optimal</span>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon m-temp"><i class="fas fa-thermometer-half"></i></div>
        <div class="metric-label">Temperature</div>
        <div class="metric-value">
            <span>{{ $stats['avg_temperature'] ?? 30.2 }}°C</span>
            <span class="metric-status status-normal">Normal</span>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon m-humidity"><i class="fas fa-cloud-rain"></i></div>
        <div class="metric-label">Humidity</div>
        <div class="metric-value">
            <span>{{ $stats['avg_humidity'] ?? 70 }}%</span>
            <span class="metric-status status-optimal">Optimal</span>
        </div>
    </div>
    <div class="metric-card">
        <div class="metric-icon m-ph"><i class="fas fa-flask"></i></div>
        <div class="metric-label">Soil pH</div>
        <div class="metric-value">
            <span>6.5</span>
            <span class="metric-status status-optimal">Optimal</span>
        </div>
    </div>
</div>

<!-- Dashboard Main Grid -->
<div class="dashboard-grid">
    <!-- Left Col -->
    <div>
        <!-- Chart -->
        <div class="chart-box">
            <div class="chart-header">
                <div class="chart-title">Soil Moisture (7 Days)</div>
                <select style="border: 1px solid var(--border-color); border-radius: 6px; padding: 0.4rem; font-size: 0.85rem; color: var(--text-main); background: #f8fafc; outline: none;">
                    <option>Last 7 Days</option>
                    <option>Last 24 Hours</option>
                    <option>Last 30 Days</option>
                </select>
            </div>
            <div id="main-chart" style="height: 300px;"></div>
        </div>

        <!-- Recent Alerts -->
        <div class="recent-alerts">
            <div class="alert-header">
                <div class="chart-title">Recent Alerts</div>
                <a href="{{ route('alerts.index') }}" style="color: var(--primary); font-size: 0.85rem; font-weight: 600; text-decoration: none;">View all</a>
            </div>
            <div class="alert-list">
                @forelse($recentAlerts as $alert)
                    <div class="alert-item">
                        <div class="alert-icon" style="color: {{ in_array($alert->type, ['critical', 'warning']) ? '#f97316' : '#3b82f6' }}">
                            <i class="fas {{ in_array($alert->type, ['critical', 'warning']) ? 'fa-exclamation-triangle' : 'fa-info-circle' }}"></i>
                        </div>
                        <div class="alert-content">
                            <h4>{{ $alert->title }}</h4>
                            <p>{{ $alert->message }}</p>
                        </div>
                        <div class="alert-time">{{ $alert->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                        <i class="fas fa-check-circle" style="font-size: 2rem; color: #22c55e; margin-bottom: 0.75rem;"></i>
                        <p style="font-size: 0.9rem; font-weight: 600;">All clear — no recent alerts</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Right Col -->
    <div>
        <!-- Irrigation Donut -->
        <div class="irrigation-box">
            <div style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                <div class="chart-title">Irrigation Status</div>
                <a href="{{ route('irrigation.index') }}" style="color: var(--primary); font-size: 0.8rem; font-weight: 600; text-decoration: none;"><i class="fas fa-cog"></i></a>
            </div>
            
            <div class="donut-ring">
                <div class="donut-content">
                    <span class="donut-val">{{ $stats['water_level'] ?? 60 }}%</span>
                    <span class="donut-label">Water Tank</span>
                </div>
            </div>

            <div style="width: 100%; display: flex; justify-content: space-between; align-items: center; margin-top: 1rem;">
                <div style="text-align: left;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">Pump Status</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Last run: {{ $irrigationActive ? 'Active now' : '2 hrs ago' }}</div>
                </div>
                <div class="pump-status" style="background: {{ $irrigationActive ? 'var(--primary)' : '#94a3b8' }};">
                    {{ $irrigationActive ? 'ON' : 'OFF' }}
                </div>
            </div>

            <button class="btn btn-outline" style="width: 100%; margin-top: 1.5rem; justify-content: center;">Manage Irrigation</button>
        </div>
        
        <!-- Connected Sensors Quick View -->
        <div class="recent-alerts" style="margin-top: 1.5rem;">
             <div class="alert-header">
                <div class="chart-title">IoT Sensors</div>
                <span class="metric-status status-optimal">{{ $stats['online_sensors'] ?? 0 }} / {{ $stats['total_sensors'] ?? 0 }} Online</span>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                @forelse($sensors->take(3) ?? [] as $sensor)
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: #f8fafc; border-radius: 8px; border: 1px solid var(--border-color);">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <i class="fas fa-microchip" style="color: var(--text-muted);"></i>
                        <div>
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-main);">{{ $sensor->name }}</div>
                            <div style="font-size: 0.75rem; color: var(--text-muted);">Battery: {{ $sensor->battery_level ?? 0 }}%</div>
                        </div>
                    </div>
                    <div style="width: 8px; height: 8px; border-radius: 50%; background: {{ $sensor->status === 'online' ? '#22c55e' : '#ef4444' }};"></div>
                </div>
                @empty
                <div style="text-align: center; padding: 1rem; color: var(--text-muted); font-size: 0.85rem;">
                    No sensors paired yet.
                </div>
                @endforelse
            </div>
            <a href="{{ route('sensors.index') }}" style="display: block; text-align: center; margin-top: 1rem; color: var(--primary); font-size: 0.85rem; font-weight: 600; text-decoration: none;">View All Sensors</a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Premium Light Chart configuration to match image mockup
    var options = {
        series: [{
            name: 'Moisture (%)',
            data: {!! json_encode($chartData['moisture'] ?? [30, 40, 35, 50, 49, 60, 70, 91, 125]) !!}
        }],
        chart: {
            height: 300,
            type: 'area',
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false }
        },
        colors: ['#219653'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
            categories: {!! json_encode($chartData['labels'] ?? ['May 10', 'May 11', 'May 12', 'May 13', 'May 14', 'May 15', 'May 16', 'May 17', 'May 18']) !!},
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } }
        },
        yaxis: {
            labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } },
            min: 0,
            max: 100,
            tickAmount: 4
        },
        grid: {
            borderColor: '#f1f5f9',
            strokeDashArray: 0,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
        },
        markers: {
            size: 5,
            colors: ['#fff'],
            strokeColors: '#219653',
            strokeWidth: 2,
            hover: { size: 7 }
        },
        tooltip: { theme: 'light' }
    };

    var chart = new ApexCharts(document.querySelector("#main-chart"), options);
    chart.render();

    @if($activeFarm)
    // Real-time updates via Laravel Echo
    if (window.Echo) {
        window.Echo.private('farm.{{ $activeFarm->id }}')
            .listen('SensorDataUpdated', (e) => {
                const moistureEl = document.getElementById('stat-moisture');
                if (moistureEl && e.soil_moisture) {
                    moistureEl.innerText = e.soil_moisture + '%';
                }
            });
    }
    @endif
});
</script>
@endsection
