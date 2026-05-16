@extends('layouts.app')
@section('title', 'IoT Sensors')
@section('breadcrumb', 'Monitoring / IoT Sensors')

@section('content')
<style>
    .sensor-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .sensor-filter { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; }
    .filter-search { position: relative; flex: 1; min-width: 200px; }
    .filter-search i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.8rem; }
    .filter-input { width: 100%; padding: 0.6rem 0.75rem 0.6rem 2.25rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; color: var(--text-main); background: var(--bg-body); outline: none; transition: all 0.2s; }
    .filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33,150,83,0.1); }
    .filter-select { padding: 0.6rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.85rem; color: var(--text-main); background: var(--bg-body); outline: none; min-width: 140px; }
    .filter-btn { padding: 0.6rem 1.25rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; }
    .filter-btn:hover { background: var(--primary-dark); }
    .sensor-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; }
    .sensor-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; transition: all 0.25s; text-decoration: none; display: block; }
    .sensor-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.06); border-color: var(--primary); }
    .sensor-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .sensor-info { display: flex; align-items: center; gap: 0.75rem; }
    .sensor-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--primary-light); display: flex; align-items: center; justify-content: center; }
    .sensor-icon i { font-size: 1.1rem; color: var(--primary); }
    .sensor-icon.offline { background: #fee2e2; }
    .sensor-icon.offline i { color: #ef4444; }
    .sensor-name { font-size: 0.9rem; font-weight: 700; color: var(--text-main); }
    .sensor-id { font-size: 0.7rem; color: var(--text-muted); font-family: monospace; margin-top: 0.15rem; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; }
    .status-dot.online { background: #22c55e; box-shadow: 0 0 8px rgba(34,197,94,0.5); }
    .status-dot.offline { background: #ef4444; box-shadow: 0 0 8px rgba(239,68,68,0.5); }
    .sensor-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; margin-bottom: 1rem; }
    .meta-box { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; padding: 0.6rem 0.75rem; }
    .meta-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 0.25rem; }
    .meta-value { font-size: 0.8rem; font-weight: 600; color: var(--text-main); text-transform: capitalize; }
    .battery-bar { flex: 1; height: 4px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
    .battery-fill { height: 100%; border-radius: 99px; }
    .battery-fill.good { background: #22c55e; }
    .battery-fill.low { background: #ef4444; }
    .sensor-footer { padding-top: 0.75rem; border-top: 1px solid var(--border-color); display: flex; flex-direction: column; gap: 0.35rem; }
    .sensor-footer-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; color: var(--text-muted); }
    .sensor-footer-row i { width: 14px; color: #94a3b8; }
    .sensor-footer-between { display: flex; justify-content: space-between; }
    .empty-sensors { grid-column: span 3; text-align: center; padding: 3rem; background: #fff; border: 2px dashed var(--border-color); border-radius: 12px; }
    .empty-sensors i { font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem; }
    @media (max-width: 1024px) { .sensor-grid { grid-template-columns: repeat(2, 1fr); } .empty-sensors { grid-column: span 2; } }
    @media (max-width: 640px) { .sensor-grid { grid-template-columns: 1fr; } .empty-sensors { grid-column: span 1; } }
</style>

<!-- Header -->
<div class="sensor-header">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">IoT Sensors</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">{{ $sensors->total() }} devices registered across all farms</p>
    </div>
</div>

<!-- Filters -->
<div class="sensor-filter">
    <form method="GET" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; width: 100%;">
        <div class="filter-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="filter-input">
        </div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Online</option>
            <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }}>Offline</option>
            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>
        <select name="type" class="filter-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="soil_moisture" {{ request('type') === 'soil_moisture' ? 'selected' : '' }}>Soil Moisture</option>
            <option value="temperature" {{ request('type') === 'temperature' ? 'selected' : '' }}>Temperature</option>
            <option value="humidity" {{ request('type') === 'humidity' ? 'selected' : '' }}>Humidity</option>
            <option value="water_level" {{ request('type') === 'water_level' ? 'selected' : '' }}>Water Level</option>
            <option value="multi" {{ request('type') === 'multi' ? 'selected' : '' }}>Multi-Sensor</option>
        </select>
        <button type="submit" class="filter-btn">Filter Results</button>
    </form>
</div>

<!-- Sensor Cards -->
<div class="sensor-grid">
    @forelse($sensors as $sensor)
    <a href="{{ route('sensors.show', $sensor) }}" class="sensor-card">
        <div class="sensor-top">
            <div class="sensor-info">
                <div class="sensor-icon {{ $sensor->status !== 'online' ? 'offline' : '' }}">
                    <i class="fas fa-microchip"></i>
                </div>
                <div>
                    <div class="sensor-name">{{ $sensor->name }}</div>
                    <div class="sensor-id">{{ $sensor->device_id }}</div>
                </div>
            </div>
            <div class="status-dot {{ $sensor->status === 'online' ? 'online' : 'offline' }}"></div>
        </div>

        <div class="sensor-meta">
            <div class="meta-box">
                <div class="meta-label">Type</div>
                <div class="meta-value">{{ str_replace('_', ' ', $sensor->type) }}</div>
            </div>
            <div class="meta-box">
                <div class="meta-label">Battery</div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.15rem;">
                    <div class="battery-bar">
                        <div class="battery-fill {{ ($sensor->battery_level ?? 0) > 20 ? 'good' : 'low' }}" style="width: {{ $sensor->battery_level ?? 0 }}%"></div>
                    </div>
                    <span class="meta-value" style="font-family: monospace; font-size: 0.75rem;">{{ number_format($sensor->battery_level ?? 0, 1) }}%</span>
                </div>
            </div>
        </div>

        <div class="sensor-footer">
            @if($sensor->latestReading)
            <div class="sensor-footer-row" id="sensor-ping-{{ $sensor->id }}">
                <i class="fas fa-satellite-dish" style="color: #22c55e;"></i>
                <span>Pinged {{ $sensor->last_reading_at?->diffForHumans() }}</span>
            </div>
            @else
            <div class="sensor-footer-row">
                <i class="fas fa-satellite-dish"></i>
                <span>Awaiting first transmission</span>
            </div>
            @endif
            <div class="sensor-footer-row sensor-footer-between">
                <span><i class="fas fa-tractor"></i> {{ $sensor->farm->name }}</span>
                <span>{{ $sensor->field?->name ?? 'Unassigned' }}</span>
            </div>
        </div>
    </a>
    @empty
    <div class="empty-sensors">
        <i class="fas fa-microchip"></i>
        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No sensors found</h3>
        <p style="font-size: 0.9rem; color: var(--text-muted);">Adjust your filters or pair a new device from a Farm dashboard.</p>
    </div>
    @endforelse
</div>

<div style="margin-top: 1.5rem;">{{ $sensors->links() }}</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.Echo) {
        const farmIds = @json($farmIds ?? []);
        
        farmIds.forEach(id => {
            window.Echo.private('farm.' + id)
                .listen('SensorDataUpdated', (e) => {
                    const sensorEl = document.getElementById('sensor-ping-' + e.sensor_id);
                    if (sensorEl) {
                        sensorEl.innerHTML = '<i class="fas fa-satellite-dish" style="color: #22c55e;"></i><span style="color: var(--primary); font-weight: 600;">Pinged just now</span>';
                    }
                });
        });
    }
});
</script>
@endsection
