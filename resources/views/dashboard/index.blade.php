@extends('layouts.app')
@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
<style>
/* ── Layout ─────────────────────────────────────────── */
.db-header{display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;margin-bottom:1.75rem}
.db-title{font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0}
.db-sub{color:var(--text-muted);font-size:.875rem;margin-top:.25rem;display:flex;align-items:center;gap:.5rem}
.live-dot{width:8px;height:8px;border-radius:50%;background:#22c55e;display:inline-block;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.6;transform:scale(1.3)}}

/* ── KPI strip ──────────────────────────────────────── */
.kpi-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:1.5rem}
.kpi{background:#fff;border:1px solid var(--border-color);border-radius:14px;padding:1.25rem 1.5rem;position:relative;overflow:hidden;transition:box-shadow .2s}
.kpi:hover{box-shadow:0 8px 24px rgba(0,0,0,.06)}
.kpi-accent{position:absolute;top:0;left:0;width:4px;height:100%;border-radius:14px 0 0 14px}
.kpi-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem}
.kpi-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.kpi-badge{font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:999px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
.kpi-val{font-size:2rem;font-weight:800;color:var(--text-main);line-height:1;margin-bottom:.35rem}
.kpi-label{font-size:.75rem;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px}
.kpi-trend{font-size:.75rem;color:var(--text-muted);margin-top:.4rem;display:flex;align-items:center;gap:.3rem}

/* colours */
.acc-blue{background:#3b82f6}.ic-blue{background:#dbeafe;color:#3b82f6}
.acc-orange{background:#f97316}.ic-orange{background:#ffedd5;color:#f97316}
.acc-cyan{background:#06b6d4}.ic-cyan{background:#cffafe;color:#06b6d4}
.acc-green{background:#22c55e}.ic-green{background:#dcfce7;color:#22c55e}
.badge-ok{background:#dcfce7;color:#166534}
.badge-warn{background:#fef3c7;color:#92400e}
.badge-alert{background:#fee2e2;color:#b91c1c}
.badge-info{background:#dbeafe;color:#1e40af}
</style>

<style>
/* ── Main grid ──────────────────────────────────────── */
.db-grid{display:grid;grid-template-columns:1fr 340px;gap:1.25rem;margin-bottom:1.25rem}
.db-grid-bottom{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.25rem}
.panel{background:#fff;border:1px solid var(--border-color);border-radius:14px;overflow:hidden}
.panel-head{display:flex;justify-content:space-between;align-items:center;padding:1.1rem 1.5rem;border-bottom:1px solid var(--border-color)}
.panel-title{font-size:.95rem;font-weight:700;color:var(--text-main)}
.panel-body{padding:1.25rem 1.5rem}

/* ── Sensor cards ───────────────────────────────────── */
.sensor-row{display:flex;align-items:center;gap:.75rem;padding:.75rem;border-radius:10px;border:1px solid var(--border-color);margin-bottom:.6rem;transition:all .2s}
.sensor-row:hover{border-color:var(--primary);background:rgba(33,150,83,.02)}
.sensor-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0}
.dot-online{background:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.2)}
.dot-offline{background:#ef4444}
.sensor-bar-wrap{flex:1;height:5px;background:#e2e8f0;border-radius:99px;overflow:hidden}
.sensor-bar{height:100%;border-radius:99px;transition:width .6s ease}

/* ── Alert items ────────────────────────────────────── */
.alert-row{display:flex;gap:.9rem;align-items:flex-start;padding:.85rem 0;border-bottom:1px solid var(--border-color)}
.alert-row:last-child{border-bottom:none;padding-bottom:0}
.alert-dot{width:8px;height:8px;border-radius:50%;margin-top:.35rem;flex-shrink:0}
.dot-high{background:#ef4444}.dot-medium{background:#f59e0b}.dot-low{background:#3b82f6}

/* ── Weather mini ───────────────────────────────────── */
.wx-mini{display:flex;align-items:center;gap:1rem;padding:1rem 1.5rem;border-bottom:1px solid var(--border-color)}
.wx-temp-big{font-size:2.5rem;font-weight:300;color:var(--text-main);line-height:1}
.wx-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem;padding:1rem 1.5rem}
.wx-item{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--text-muted)}
.wx-item i{width:16px;color:#94a3b8}

/* ── Irrigation donut ───────────────────────────────── */
.donut-wrap{display:flex;flex-direction:column;align-items:center;padding:1.25rem 1.5rem}
.donut-svg{transform:rotate(-90deg)}
.donut-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center}
.pump-row{display:flex;justify-content:space-between;align-items:center;width:100%;padding:.75rem 1.5rem;border-top:1px solid var(--border-color)}
.pump-pill{font-size:.7rem;font-weight:700;padding:.25rem .75rem;border-radius:999px;text-transform:uppercase;letter-spacing:.5px}
.pill-on{background:#dcfce7;color:#166534}.pill-off{background:#f1f5f9;color:#64748b}

/* ── Responsive ─────────────────────────────────────── */
@media(max-width:1100px){.db-grid{grid-template-columns:1fr}.db-grid-bottom{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.kpi-grid{grid-template-columns:1fr 1fr}.db-grid-bottom{grid-template-columns:1fr}}
</style>

{{-- ── Header ──────────────────────────────────────────── --}}
<div class="db-header">
    <div>
        <div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.25rem;">
            <h1 class="db-title">{{ $activeFarm->name ?? 'Farm Dashboard' }}</h1>
            @if($farms->count() > 1)
            <form method="GET" action="{{ route('dashboard') }}" style="margin:0;">
                <select name="farm_id" onchange="this.form.submit()" style="padding: 0.3rem 0.5rem; border-radius: 6px; border: 1px solid var(--border-color); font-size: 0.85rem; font-weight: 600; color: var(--text-main); background: #f8fafc; cursor: pointer; outline: none;">
                    @foreach($farms as $farm)
                        <option value="{{ $farm->id }}" {{ $activeFarm && $activeFarm->id == $farm->id ? 'selected' : '' }}>{{ $farm->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>
        <p class="db-sub">
            <span class="live-dot"></span>
            <span id="last-updated">Live · updated just now</span>
            @if($activeFarm) &nbsp;·&nbsp; {{ $activeFarm->location }} @endif
        </p>
    </div>
    <div style="display:flex;gap:.75rem;flex-wrap:wrap">
        @if($activeFarm)
        <a href="{{ route('farms.show', $activeFarm) }}" class="btn btn-outline"><i class="fas fa-plus"></i> Add Sensor</a>
        @endif
        <a href="{{ route('reports.index') }}" class="btn btn-outline"><i class="fas fa-download"></i> Export</a>
    </div>
</div>

{{-- ── KPI Strip ───────────────────────────────────────── --}}
@php
    $moisture = $stats['avg_soil_moisture'] ?? 0;
    $temp     = $stats['avg_temperature']   ?? 0;
    $humidity = $stats['avg_humidity']      ?? 0;
    $water    = $stats['water_level']       ?? 0;

    $moistureBadge = $moisture < 30 ? ['alert','Critical'] : ($moisture > 75 ? ['warn','Saturated'] : ['ok','Optimal']);
    $tempBadge     = $temp > 35     ? ['alert','High']     : ($temp < 10    ? ['warn','Low']        : ['ok','Normal']);
    $humidBadge    = $humidity < 40 ? ['warn','Low']       : ($humidity > 80? ['warn','High']       : ['ok','Optimal']);
    $waterBadge    = $water < 20    ? ['alert','Critical'] : ($water < 40   ? ['warn','Low']        : ['ok','Good']);
@endphp

<div class="kpi-grid">
    <div class="kpi">
        <div class="kpi-accent acc-blue"></div>
        <div class="kpi-top">
            <div class="kpi-icon ic-blue"><i class="fas fa-tint"></i></div>
            <span class="kpi-badge badge-{{ $moistureBadge[0] }}">{{ $moistureBadge[1] }}</span>
        </div>
        <div class="kpi-val" id="rt-moisture">{{ $moisture }}<span style="font-size:1.1rem;font-weight:500;color:var(--text-muted)">%</span></div>
        <div class="kpi-label">Soil Moisture</div>
        <div class="kpi-trend"><i class="fas fa-microchip" style="font-size:.65rem"></i> avg of {{ $stats['total_sensors'] ?? 0 }} sensors</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent acc-orange"></div>
        <div class="kpi-top">
            <div class="kpi-icon ic-orange"><i class="fas fa-thermometer-half"></i></div>
            <span class="kpi-badge badge-{{ $tempBadge[0] }}">{{ $tempBadge[1] }}</span>
        </div>
        <div class="kpi-val" id="rt-temp">{{ $temp }}<span style="font-size:1.1rem;font-weight:500;color:var(--text-muted)">°C</span></div>
        <div class="kpi-label">Temperature</div>
        <div class="kpi-trend"><i class="fas fa-clock" style="font-size:.65rem"></i> last reading</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent acc-cyan"></div>
        <div class="kpi-top">
            <div class="kpi-icon ic-cyan"><i class="fas fa-cloud-rain"></i></div>
            <span class="kpi-badge badge-{{ $humidBadge[0] }}">{{ $humidBadge[1] }}</span>
        </div>
        <div class="kpi-val" id="rt-humidity">{{ $humidity }}<span style="font-size:1.1rem;font-weight:500;color:var(--text-muted)">%</span></div>
        <div class="kpi-label">Humidity</div>
        <div class="kpi-trend"><i class="fas fa-clock" style="font-size:.65rem"></i> last reading</div>
    </div>
    <div class="kpi">
        <div class="kpi-accent acc-green"></div>
        <div class="kpi-top">
            <div class="kpi-icon ic-green"><i class="fas fa-water"></i></div>
            <span class="kpi-badge badge-{{ $waterBadge[0] }}">{{ $waterBadge[1] }}</span>
        </div>
        <div class="kpi-val" id="rt-water">{{ $water }}<span style="font-size:1.1rem;font-weight:500;color:var(--text-muted)">%</span></div>
        <div class="kpi-label">Water Level</div>
        <div class="kpi-trend"><i class="fas fa-tint" style="font-size:.65rem"></i> tank level</div>
    </div>
</div>

{{-- ── Main Grid: Chart + Right sidebar ───────────────── --}}
<div class="db-grid">

    {{-- Left: Live Chart --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Live Sensor Readings <span class="live-dot" style="margin-left:.5rem"></span></span>
            <div style="display:flex;gap:.5rem">
                <button onclick="switchChart('moisture')" id="btn-moisture"
                    style="font-size:.75rem;font-weight:700;padding:.3rem .75rem;border-radius:6px;border:1px solid var(--primary);background:var(--primary);color:#fff;cursor:pointer">Moisture</button>
                <button onclick="switchChart('temperature')" id="btn-temperature"
                    style="font-size:.75rem;font-weight:700;padding:.3rem .75rem;border-radius:6px;border:1px solid var(--border-color);background:#fff;color:var(--text-muted);cursor:pointer">Temp</button>
                <button onclick="switchChart('humidity')" id="btn-humidity"
                    style="font-size:.75rem;font-weight:700;padding:.3rem .75rem;border-radius:6px;border:1px solid var(--border-color);background:#fff;color:var(--text-muted);cursor:pointer">Humidity</button>
            </div>
        </div>
        <div style="padding:1rem 1.5rem 1.5rem">
            <div id="live-chart" style="height:280px"></div>
        </div>
    </div>

    {{-- Right: Weather + Irrigation --}}
    <div style="display:flex;flex-direction:column;gap:1.25rem">

        {{-- Weather mini card --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title"><i class="fas fa-cloud-sun" style="color:#f59e0b;margin-right:.4rem"></i>Weather</span>
                <a href="{{ route('weather.index') }}" style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none">Details →</a>
            </div>
            @if($weather)
            <div class="wx-mini">
                <div>
                    <div class="wx-temp-big">{{ round($weather->temperature) }}<span style="font-size:1.25rem;color:var(--text-muted)">°C</span></div>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:.2rem">{{ $weather->condition }}</div>
                </div>
                <div style="flex:1;text-align:right">
                    <i class="fas fa-{{ $weather->icon ?? 'sun' }}" style="font-size:2.5rem;color:#f59e0b;opacity:.85"></i>
                </div>
            </div>
            <div class="wx-grid">
                <div class="wx-item"><i class="fas fa-tint"></i>{{ round($weather->humidity) }}% Humidity</div>
                <div class="wx-item"><i class="fas fa-wind"></i>{{ round($weather->wind_speed) }} km/h</div>
                <div class="wx-item"><i class="fas fa-cloud-showers-heavy"></i>{{ $weather->rainfall_mm }}mm Rain</div>
                <div class="wx-item"><i class="fas fa-gauge-high"></i>{{ $weather->pressure_hpa }} hPa</div>
            </div>
            @else
            <div style="padding:1.5rem;text-align:center;color:var(--text-muted);font-size:.85rem">
                <i class="fas fa-cloud" style="font-size:1.5rem;margin-bottom:.5rem;display:block;color:#cbd5e1"></i>
                No weather data yet.<br>
                <a href="{{ route('weather.index') }}" style="color:var(--primary);font-weight:600">Set up weather →</a>
            </div>
            @endif
        </div>

        {{-- Irrigation donut --}}
        <div class="panel">
            <div class="panel-head">
                <span class="panel-title"><i class="fas fa-tint" style="color:#3b82f6;margin-right:.4rem"></i>Irrigation</span>
                <a href="{{ route('irrigation.index') }}" style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none">Manage →</a>
            </div>
            <div class="donut-wrap">
                @php $pct = min(100, max(0, $water)); $circ = 2 * M_PI * 54; $dash = ($pct/100) * $circ; @endphp
                <div style="position:relative;width:130px;height:130px">
                    <svg width="130" height="130" class="donut-svg">
                        <circle cx="65" cy="65" r="54" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        <circle id="donut-arc" cx="65" cy="65" r="54" fill="none"
                            stroke="{{ $pct < 20 ? '#ef4444' : ($pct < 40 ? '#f59e0b' : '#22c55e') }}"
                            stroke-width="12" stroke-linecap="round"
                            stroke-dasharray="{{ round($dash,1) }} {{ round($circ,1) }}"
                            style="transition:stroke-dasharray .8s ease"/>
                    </svg>
                    <div class="donut-center">
                        <div style="font-size:1.4rem;font-weight:800;color:var(--text-main)" id="donut-val">{{ $pct }}%</div>
                        <div style="font-size:.65rem;font-weight:600;color:var(--text-muted)">WATER TANK</div>
                    </div>
                </div>
            </div>
            <div class="pump-row">
                <div>
                    <div style="font-size:.85rem;font-weight:700;color:var(--text-main)">Pump Status</div>
                    <div style="font-size:.72rem;color:var(--text-muted);margin-top:.15rem" id="pump-sub">
                        {{ $irrigationActive ? $activeIrrigationCount . ' zone(s) active' : 'Idle' }}
                    </div>
                </div>
                <span class="pump-pill {{ $irrigationActive ? 'pill-on' : 'pill-off' }}" id="pump-pill">
                    {{ $irrigationActive ? 'ON' : 'OFF' }}
                </span>
            </div>
        </div>

    </div>
</div>

{{-- ── Bottom Grid: Alerts · Sensors · Farm Stats ──────── --}}
<div class="db-grid-bottom">

    {{-- Recent Alerts --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Recent Alerts</span>
            <a href="{{ route('alerts.index') }}" style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none">View all →</a>
        </div>
        <div class="panel-body" style="padding-top:.5rem;padding-bottom:.5rem" id="alert-feed">
            @forelse($recentAlerts as $alert)
            <div class="alert-row">
                <div class="alert-dot dot-{{ $alert->priority ?? 'low' }}" style="margin-top:.45rem"></div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.875rem;font-weight:700;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $alert->title }}</div>
                    <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;line-height:1.4">{{ Str::limit($alert->message, 70) }}</div>
                </div>
                <div style="font-size:.7rem;color:var(--text-muted);white-space:nowrap;margin-left:.5rem">{{ $alert->created_at->diffForHumans(null, true) }}</div>
            </div>
            @empty
            <div style="text-align:center;padding:2rem;color:var(--text-muted)">
                <i class="fas fa-check-circle" style="font-size:1.75rem;color:#22c55e;display:block;margin-bottom:.5rem"></i>
                <span style="font-size:.875rem;font-weight:600">All clear</span>
            </div>
            @endforelse
        </div>
    </div>

    {{-- IoT Sensors --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">IoT Sensors</span>
            <span style="font-size:.75rem;font-weight:700;padding:.2rem .6rem;border-radius:999px;background:{{ $stats['online_sensors'] > 0 ? '#dcfce7' : '#fee2e2' }};color:{{ $stats['online_sensors'] > 0 ? '#166534' : '#b91c1c' }}">
                {{ $stats['online_sensors'] ?? 0 }}/{{ $stats['total_sensors'] ?? 0 }} Online
            </span>
        </div>
        <div class="panel-body">
            @forelse($sensors->take(5) as $sensor)
            @php
                $val = $sensor->latestReading?->soil_moisture ?? $sensor->latestReading?->temperature ?? 0;
                $barPct = min(100, max(0, $val));
                $barColor = $sensor->status === 'online' ? '#22c55e' : '#ef4444';
            @endphp
            <div class="sensor-row">
                <div class="sensor-dot {{ $sensor->status === 'online' ? 'dot-online' : 'dot-offline' }}"></div>
                <div style="flex:1;min-width:0">
                    <div style="font-size:.85rem;font-weight:700;color:var(--text-main);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $sensor->name }}</div>
                    <div style="display:flex;align-items:center;gap:.5rem;margin-top:.35rem">
                        <div class="sensor-bar-wrap">
                            <div class="sensor-bar" style="width:{{ $barPct }}%;background:{{ $barColor }}"></div>
                        </div>
                        <span style="font-size:.7rem;color:var(--text-muted);white-space:nowrap">{{ $val }}{{ str_contains($sensor->type,'temp') ? '°C' : '%' }}</span>
                    </div>
                </div>
                <div style="text-align:right;flex-shrink:0">
                    <div style="font-size:.65rem;color:var(--text-muted)">Batt</div>
                    <div style="font-size:.8rem;font-weight:700;color:{{ ($sensor->battery_level ?? 100) < 20 ? '#ef4444' : 'var(--text-main)' }}">{{ $sensor->battery_level ?? '—' }}%</div>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:1.5rem;color:var(--text-muted);font-size:.875rem">
                No sensors paired yet.
            </div>
            @endforelse
            @if($sensors->count() > 5)
            <a href="{{ route('sensors.index') }}" style="display:block;text-align:center;margin-top:.75rem;font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none">
                +{{ $sensors->count() - 5 }} more sensors →
            </a>
            @endif
        </div>
    </div>

    {{-- Farm Stats --}}
    <div class="panel">
        <div class="panel-head">
            <span class="panel-title">Farm Overview</span>
            <a href="{{ route('farms.index') }}" style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none">All farms →</a>
        </div>
        <div class="panel-body">
            @php
                $statItems = [
                    ['icon'=>'map-marked-alt','label'=>'Total Farms',   'val'=>$stats['total_farms']   ?? 0, 'color'=>'#3b82f6'],
                    ['icon'=>'layer-group',   'label'=>'Fields',        'val'=>$stats['total_fields']  ?? 0, 'color'=>'#8b5cf6'],
                    ['icon'=>'seedling',      'label'=>'Active Crops',  'val'=>$stats['total_crops']   ?? 0, 'color'=>'#22c55e'],
                    ['icon'=>'microchip',     'label'=>'Sensors',       'val'=>$stats['total_sensors'] ?? 0, 'color'=>'#f97316'],
                    ['icon'=>'bell',          'label'=>'Active Alerts', 'val'=>$stats['active_alerts'] ?? 0, 'color'=>'#ef4444'],
                    ['icon'=>'tint',          'label'=>'Irrigation',    'val'=>$irrigationActive ? 'Active' : 'Idle', 'color'=>'#06b6d4'],
                ];
            @endphp
            @foreach($statItems as $item)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 0;border-bottom:1px solid var(--border-color)">
                <div style="display:flex;align-items:center;gap:.65rem">
                    <div style="width:30px;height:30px;border-radius:8px;background:{{ $item['color'] }}18;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-{{ $item['icon'] }}" style="font-size:.8rem;color:{{ $item['color'] }}"></i>
                    </div>
                    <span style="font-size:.85rem;color:var(--text-muted);font-weight:600">{{ $item['label'] }}</span>
                </div>
                <span style="font-size:.95rem;font-weight:800;color:var(--text-main)">{{ $item['val'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Chart data from server ──────────────────────────────
    const chartData = {
        moisture:    @json($chartData['moisture']    ?? []),
        temperature: @json($chartData['temperature'] ?? []),
        humidity:    @json($chartData['humidity']    ?? []),
        labels:      @json($chartData['labels']      ?? []),
    };

    const seriesConfig = {
        moisture:    { name: 'Soil Moisture (%)', color: '#3b82f6', min: 0,   max: 100 },
        temperature: { name: 'Temperature (°C)',  color: '#f97316', min: 0,   max: 50  },
        humidity:    { name: 'Humidity (%)',       color: '#06b6d4', min: 0,   max: 100 },
    };

    let activeMetric = 'moisture';

    const chartOptions = (metric) => ({
        series: [{ name: seriesConfig[metric].name, data: chartData[metric] }],
        chart: {
            type: 'area', height: 280,
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false }, zoom: { enabled: false },
            animations: { enabled: true, easing: 'easeinout', speed: 600 },
        },
        colors: [seriesConfig[metric].color],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 90, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            categories: chartData.labels,
            axisBorder: { show: false }, axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontSize: '10px' }, rotate: -30 },
        },
        yaxis: {
            min: seriesConfig[metric].min, max: seriesConfig[metric].max,
            tickAmount: 4,
            labels: { style: { colors: '#94a3b8', fontSize: '10px' } },
        },
        grid: { borderColor: '#f1f5f9', strokeDashArray: 3, xaxis: { lines: { show: false } } },
        markers: { size: 0, hover: { size: 5 } },
        tooltip: { theme: 'light', x: { show: true } },
    });

    const chart = new ApexCharts(document.querySelector('#live-chart'), chartOptions('moisture'));
    chart.render();

    // ── Chart switcher buttons ──────────────────────────────
    window.switchChart = function (metric) {
        activeMetric = metric;
        chart.updateOptions(chartOptions(metric), true, true);
        ['moisture', 'temperature', 'humidity'].forEach(m => {
            const btn = document.getElementById('btn-' + m);
            if (m === metric) {
                btn.style.background = 'var(--primary)';
                btn.style.color = '#fff';
                btn.style.borderColor = 'var(--primary)';
            } else {
                btn.style.background = '#fff';
                btn.style.color = 'var(--text-muted)';
                btn.style.borderColor = 'var(--border-color)';
            }
        });
    };

    // ── Real-time: SensorDataUpdated ───────────────────────
    @if($activeFarm)
    if (window.Echo) {
        window.Echo.private('farm.{{ $activeFarm->id }}')

            .listen('SensorDataUpdated', (e) => {
                // Update KPI tiles
                if (e.soil_moisture !== null) setKpi('rt-moisture', e.soil_moisture, '%');
                if (e.temperature   !== null) setKpi('rt-temp',     e.temperature,   '°C');
                if (e.humidity      !== null) setKpi('rt-humidity',  e.humidity,      '%');

                // Append new point to live chart
                if (e[activeMetric] !== null && e[activeMetric] !== undefined) {
                    chartData[activeMetric].push(e[activeMetric]);
                    chartData.labels.push(e.recorded_at);
                    // Keep last 48 points
                    if (chartData[activeMetric].length > 48) {
                        chartData[activeMetric].shift();
                        chartData.labels.shift();
                    }
                    chart.updateOptions(chartOptions(activeMetric), false, false);
                }

                // Timestamp
                document.getElementById('last-updated').textContent = 'Live · updated just now';
            })

            .listen('.alert.created', (e) => {
                // Prepend new alert row to feed
                const feed = document.getElementById('alert-feed');
                const dot  = e.priority === 'high' ? '#ef4444' : (e.priority === 'medium' ? '#f59e0b' : '#3b82f6');
                const row  = `<div class="alert-row" style="animation:fadeIn .4s ease">
                    <div class="alert-dot" style="background:${dot};margin-top:.45rem;width:8px;height:8px;border-radius:50%;flex-shrink:0"></div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.875rem;font-weight:700;color:var(--text-main)">${e.title ?? 'New Alert'}</div>
                        <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem">${(e.message ?? '').substring(0,70)}</div>
                    </div>
                    <div style="font-size:.7rem;color:var(--text-muted);white-space:nowrap;margin-left:.5rem">just now</div>
                </div>`;
                feed.insertAdjacentHTML('afterbegin', row);
                // Keep max 5 rows
                const rows = feed.querySelectorAll('.alert-row');
                if (rows.length > 5) rows[rows.length - 1].remove();
            })

            .listen('IrrigationStatusChanged', (e) => {
                const isOn  = e.status === 'active';
                const pill  = document.getElementById('pump-pill');
                const sub   = document.getElementById('pump-sub');
                if (pill) { pill.textContent = isOn ? 'ON' : 'OFF'; pill.className = 'pump-pill ' + (isOn ? 'pill-on' : 'pill-off'); }
                if (sub)  { sub.textContent  = isOn ? 'Running now' : 'Idle'; }
            });
    }
    @endif

    // ── Helper: update KPI value with flash ────────────────
    function setKpi(id, val, unit) {
        const el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = val + `<span style="font-size:1.1rem;font-weight:500;color:var(--text-muted)">${unit}</span>`;
        el.style.transition = 'color .3s';
        el.style.color = '#22c55e';
        setTimeout(() => { el.style.color = 'var(--text-main)'; }, 800);
    }

    // ── Fade-in keyframe ───────────────────────────────────
    const style = document.createElement('style');
    style.textContent = '@keyframes fadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(style);
});
</script>
@endsection
