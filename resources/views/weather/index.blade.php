@extends('layouts.app')
@section('title', 'Weather')
@section('breadcrumb', 'Monitoring / Weather')

@section('content')
<style>
    .wx-hero { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 2rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 2rem; flex-wrap: wrap; }
    .wx-main { display: flex; align-items: center; gap: 2rem; }
    .wx-icon-box { width: 80px; height: 80px; border-radius: 16px; background: linear-gradient(135deg, #fef3c7, #fde68a); display: flex; align-items: center; justify-content: center; }
    .wx-icon-box i { font-size: 2.5rem; color: #f59e0b; }
    .wx-temp { font-size: 3.5rem; font-weight: 300; color: var(--text-main); line-height: 1; }
    .wx-temp span { font-size: 2rem; color: var(--text-muted); }
    .wx-condition { font-size: 1.1rem; font-weight: 600; color: var(--text-main); margin-top: 0.25rem; }
    .wx-time { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; }
    .wx-detail-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
    .wx-detail { text-align: center; }
    .wx-detail-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 0.5rem; }
    .wx-detail-value { font-size: 1.5rem; font-weight: 600; color: var(--text-main); }
    .wx-detail-value span { font-size: 0.85rem; color: var(--text-muted); margin-left: 0.15rem; }
    .wx-metrics { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .wx-metric { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; display: flex; align-items: center; gap: 0.75rem; transition: all 0.2s; }
    .wx-metric:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    .wx-metric-icon { width: 40px; height: 40px; border-radius: 10px; background: var(--bg-body); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; }
    .wx-metric-icon i { color: #64748b; }
    .wx-metric-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
    .wx-metric-value { font-size: 0.9rem; font-weight: 700; color: var(--text-main); }
    .wx-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
    .wx-card-title { font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem; }
    .wx-forecast-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 0.75rem; }
    .wx-day { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; display: flex; flex-direction: column; align-items: center; text-align: center; transition: all 0.2s; }
    .wx-day:hover { border-color: var(--primary); }
    .wx-day.today { border-color: var(--primary); background: rgba(33,150,83,0.03); }
    .wx-day-name { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.5rem; }
    .wx-day-icon { font-size: 1.25rem; color: #64748b; margin-bottom: 0.5rem; }
    .wx-day-temp { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    .wx-day-meta { width: 100%; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px solid var(--border-color); }
    .wx-day-meta div { display: flex; justify-content: space-between; font-size: 0.7rem; color: var(--text-muted); margin-bottom: 0.2rem; }
    .today-badge { font-size: 0.6rem; font-weight: 700; background: rgba(33,150,83,0.1); color: var(--primary); padding: 0.15rem 0.5rem; border-radius: 999px; margin-top: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .empty-wx { text-align: center; padding: 4rem; background: #fff; border: 2px dashed var(--border-color); border-radius: 12px; }
    @media (max-width: 1024px) { .wx-metrics { grid-template-columns: repeat(2, 1fr); } .wx-forecast-grid { grid-template-columns: repeat(4, 1fr); } }
    @media (max-width: 640px) { .wx-forecast-grid { grid-template-columns: repeat(2, 1fr); } .wx-hero { flex-direction: column; } }
</style>

<div style="margin-bottom: 1.5rem; display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Weather Dashboard</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">{{ $activeFarm?->location ?? 'No farm selected' }}</p>
    </div>

    {{-- Farm switcher --}}
    @if($farms->count() > 1)
    <form method="GET" action="{{ route('weather.index') }}" style="display: flex; align-items: center; gap: 0.5rem;">
        <select name="farm_id" onchange="this.form.submit()"
            style="padding: 0.5rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.85rem; background: #fff; color: var(--text-main); outline: none;">
            @foreach($farms as $farm)
                <option value="{{ $farm->id }}" {{ $activeFarm?->id == $farm->id ? 'selected' : '' }}>
                    {{ $farm->name }}
                </option>
            @endforeach
        </select>
    </form>
    @endif
</div>

{{-- Missing coordinates warning --}}
@if($missingCoords)
<div style="background: #fef3c7; border: 1px solid #fde68a; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
    <i class="fas fa-map-marker-alt" style="color: #d97706; font-size: 1.25rem;"></i>
    <div>
        <p style="font-weight: 700; color: #92400e; margin: 0 0 0.25rem;">No coordinates set for <em>{{ $activeFarm->name }}</em></p>
        <p style="font-size: 0.85rem; color: #78350f; margin: 0;">
            Add a latitude and longitude to this farm so weather data can be fetched automatically.
            Go to <a href="{{ route('farms.index') }}" style="color: #d97706; font-weight: 600;">Farms</a> and edit the farm to add its location.
        </p>
    </div>
</div>
@endif

@if($currentWeather)
<!-- Hero -->
<div class="wx-hero">
    <div class="wx-main">
        <div class="wx-icon-box">
            <i class="fas fa-{{ $currentWeather->icon ?? 'sun' }}"></i>
        </div>
        <div>
            <div class="wx-temp">{{ round($currentWeather->temperature) }}<span>°C</span></div>
            <div class="wx-condition">{{ $currentWeather->condition }}</div>
            <div class="wx-time">{{ $currentWeather->recorded_at->format('D, d M Y · h:i A') }}</div>
        </div>
    </div>
    <div class="wx-detail-grid">
        <div class="wx-detail">
            <div class="wx-detail-label">Humidity</div>
            <div class="wx-detail-value">{{ round($currentWeather->humidity) }}%</div>
        </div>
        <div class="wx-detail">
            <div class="wx-detail-label">Wind</div>
            <div class="wx-detail-value">{{ round($currentWeather->wind_speed) }}<span>km/h</span></div>
        </div>
        <div class="wx-detail">
            <div class="wx-detail-label">Rainfall</div>
            <div class="wx-detail-value">{{ $currentWeather->rainfall_mm }}<span>mm</span></div>
        </div>
    </div>
</div>

<!-- Metrics -->
<div class="wx-metrics">
    <div class="wx-metric">
        <div class="wx-metric-icon"><i class="fas fa-compass"></i></div>
        <div><div class="wx-metric-label">Direction</div><div class="wx-metric-value">{{ $currentWeather->wind_direction }}</div></div>
    </div>
    <div class="wx-metric">
        <div class="wx-metric-icon"><i class="fas fa-sun"></i></div>
        <div><div class="wx-metric-label">UV Index</div><div class="wx-metric-value">{{ $currentWeather->uv_index }}</div></div>
    </div>
    <div class="wx-metric">
        <div class="wx-metric-icon"><i class="fas fa-eye"></i></div>
        <div><div class="wx-metric-label">Visibility</div><div class="wx-metric-value">{{ $currentWeather->visibility_km }} km</div></div>
    </div>
    <div class="wx-metric">
        <div class="wx-metric-icon"><i class="fas fa-gauge-high"></i></div>
        <div><div class="wx-metric-label">Pressure</div><div class="wx-metric-value">{{ $currentWeather->pressure_hpa }} hPa</div></div>
    </div>
</div>

<!-- 7-Day Forecast -->
<div class="wx-card">
    <div class="wx-card-title">7-Day History</div>
    <div class="wx-forecast-grid">
        @foreach($forecast as $day)
        <div class="wx-day {{ $loop->last ? 'today' : '' }}">
            <div class="wx-day-name">{{ $day->recorded_at->format('D') }}</div>
            <i class="fas fa-{{ $day->icon ?? 'sun' }} wx-day-icon"></i>
            <div class="wx-day-temp">{{ round($day->temperature) }}°</div>
            <div class="wx-day-meta">
                <div><span><i class="fas fa-tint"></i></span><span>{{ round($day->humidity) }}%</span></div>
                <div><span><i class="fas fa-wind"></i></span><span>{{ round($day->wind_speed) }}</span></div>
            </div>
            @if($loop->last)<div class="today-badge">Today</div>@endif
        </div>
        @endforeach
    </div>
</div>

<!-- Temp Chart -->
<div class="wx-card">
    <div class="wx-card-title">Temperature Trend</div>
    <div id="weather-chart" style="height: 300px;"></div>
</div>
@else
<div class="empty-wx">
    <i class="fas fa-cloud" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No weather data yet</h3>
    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 0.5rem;">
        Weather is fetched automatically every 30 minutes once your farm has coordinates set.
    </p>
    <p style="font-size: 0.85rem; color: var(--text-muted);">
        You can also run it manually:<br>
        <code style="background: #f1f5f9; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">php artisan weather:fetch</code>
    </p>
</div>
@endif
@endsection

@section('scripts')
@if($forecast->count() > 0)
<script>
document.addEventListener('DOMContentLoaded', function() {
    new ApexCharts(document.querySelector('#weather-chart'), {
        series: [{name:'Temperature',data:@json($forecast->pluck('temperature')->map(fn($v)=>round((float)$v)))},{name:'Humidity',data:@json($forecast->pluck('humidity')->map(fn($v)=>round((float)$v)))}],
        chart: {type:'bar',height:250,fontFamily:'Inter',toolbar:{show:false},background:'transparent'},
        colors: ['#ef4444','#3b82f6'],
        plotOptions: {bar:{borderRadius:6,columnWidth:'50%'}},
        grid: {borderColor:'#e2e8f0',strokeDashArray:3},
        xaxis: {categories:@json($forecast->pluck('recorded_at')->map(fn($d)=>$d->format('D'))),labels:{style:{colors:'#64748b',fontSize:'10px'}},axisBorder:{show:false}},
        yaxis: {labels:{style:{colors:'#64748b',fontSize:'10px'}}},
        legend: {labels:{colors:'#64748b'},fontSize:'12px'},
        tooltip: {theme:'light'},
        dataLabels: {enabled:false},
    }).render();
});
</script>
@endif
@endsection
