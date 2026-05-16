@extends('layouts.app')
@section('title', $sensor->name)
@section('breadcrumb', 'Sensors / ' . $sensor->name)

@section('content')
<style>
.sd-back{display:flex;align-items:center;gap:.75rem;margin-bottom:1.5rem}
.sd-back a{color:var(--text-muted);text-decoration:none;transition:color .2s}
.sd-back a:hover{color:var(--primary)}
.sd-status{margin-left:auto;display:flex;align-items:center;gap:.5rem}
.sd-dot{width:10px;height:10px;border-radius:50%}
.sd-dot.on{background:#22c55e;box-shadow:0 0 8px rgba(34,197,94,.5)}
.sd-dot.off{background:#ef4444;box-shadow:0 0 8px rgba(239,68,68,.5)}
.sd-info-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;margin-bottom:1.5rem}
.sd-info{background:#fff;border:1px solid var(--border-color);border-radius:10px;padding:1rem}
.sd-info-label{font-size:.7rem;color:var(--text-muted);font-weight:600;margin-bottom:.25rem}
.sd-info-value{font-size:.9rem;font-weight:700;color:var(--text-main)}
.sd-card{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem}
.sd-card-title{font-size:.9rem;font-weight:700;color:var(--text-main);margin-bottom:1rem}
.sd-table{width:100%;border-collapse:collapse}
.sd-table th{padding:.6rem .75rem;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);border-bottom:1px solid var(--border-color);background:var(--bg-body)}
.sd-table td{padding:.6rem .75rem;font-size:.85rem;color:var(--text-main);border-bottom:1px solid var(--border-color)}
.sd-table tr:hover td{background:#f8fafc}
@media(max-width:768px){.sd-info-grid{grid-template-columns:repeat(2,1fr)}}
</style>

<div class="sd-back">
    <a href="{{ route('sensors.index') }}"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h1 style="font-size:1.3rem;font-weight:700;color:var(--text-main);margin:0">{{ $sensor->name }}</h1>
        <p style="font-size:.8rem;color:var(--text-muted);font-family:monospace;margin-top:.15rem">{{ $sensor->device_id }} · {{ $sensor->farm->name }}</p>
    </div>
    <div class="sd-status">
        <div class="sd-dot {{ $sensor->status === 'online' ? 'on' : 'off' }}"></div>
        <span style="font-size:.85rem;font-weight:600;color:{{ $sensor->status === 'online' ? '#22c55e' : '#ef4444' }}">{{ ucfirst($sensor->status) }}</span>
    </div>
</div>

<div class="sd-info-grid">
    <div class="sd-info"><div class="sd-info-label">Model</div><div class="sd-info-value">{{ $sensor->model ?? 'Unknown' }}</div></div>
    <div class="sd-info"><div class="sd-info-label">Firmware</div><div class="sd-info-value">{{ $sensor->firmware_version ?? 'N/A' }}</div></div>
    <div class="sd-info"><div class="sd-info-label">Battery</div><div class="sd-info-value">{{ $sensor->battery_level ? round($sensor->battery_level) . '%' : 'N/A' }}</div></div>
    <div class="sd-info"><div class="sd-info-label">Last Reading</div><div class="sd-info-value">{{ $sensor->last_reading_at?->diffForHumans() ?? 'Never' }}</div></div>
</div>

<div class="sd-card">
    <div class="sd-card-title">24h Readings</div>
    <div id="detail-chart" style="height:300px"></div>
</div>

<div class="sd-card">
    <div class="sd-card-title">Recent Readings</div>
    <div style="overflow-x:auto">
        <table class="sd-table">
            <thead><tr><th>Time</th><th>Moisture</th><th>Temp</th><th>Humidity</th><th>Water</th><th>Light</th><th>pH</th></tr></thead>
            <tbody>
                @foreach($recentReadings as $reading)
                <tr>
                    <td style="color:var(--text-muted)">{{ $reading->recorded_at->format('d M, H:i') }}</td>
                    <td>{{ $reading->soil_moisture ? $reading->soil_moisture . '%' : '—' }}</td>
                    <td>{{ $reading->temperature ? $reading->temperature . '°C' : '—' }}</td>
                    <td>{{ $reading->humidity ? $reading->humidity . '%' : '—' }}</td>
                    <td>{{ $reading->water_level ? $reading->water_level . '%' : '—' }}</td>
                    <td>{{ $reading->light_intensity ? $reading->light_intensity . ' lux' : '—' }}</td>
                    <td>{{ $reading->ph_level ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let datasets = @json($chartData['datasets']);
    let labels = @json($chartData['labels']);
    const colors = {soil_moisture:'#3b82f6',temperature:'#ef4444',humidity:'#8b5cf6',water_level:'#06b6d4',light_intensity:'#f59e0b',ph_level:'#10b981'};
    const names = {soil_moisture:'Moisture %',temperature:'Temp °C',humidity:'Humidity %',water_level:'Water %',light_intensity:'Light lux',ph_level:'pH'};

    const series = Object.keys(datasets).map(k => ({name:names[k]||k, data:datasets[k]}));
    const chartColors = Object.keys(datasets).map(k => colors[k]||'#64748b');

    const chart = new ApexCharts(document.querySelector('#detail-chart'), {
        series, chart:{type:'line',height:300,fontFamily:'Inter',toolbar:{show:false},background:'transparent',animations:{enabled:true,easing:'linear',dynamicAnimation:{speed:1000}}},
        colors:chartColors, stroke:{curve:'smooth',width:2},
        grid:{borderColor:'#e2e8f0',strokeDashArray:3},
        xaxis:{categories:labels,labels:{style:{colors:'#64748b',fontSize:'10px'}},axisBorder:{show:false},axisTicks:{show:false},tickAmount:10},
        yaxis:{labels:{style:{colors:'#64748b',fontSize:'10px'}}},
        legend:{labels:{colors:'#64748b'},fontSize:'12px'}, tooltip:{theme:'light'}, dataLabels:{enabled:false},
    });
    chart.render();

    window.addEventListener('sensor-reading-updated', (e) => {
        const payload = e.detail;
        if (payload.sensor.id === {{ $sensor->id }}) {
            const now = new Date();
            const timeStr = now.getHours().toString().padStart(2,'0')+':'+now.getMinutes().toString().padStart(2,'0');
            labels.push(timeStr);
            if (labels.length > 50) labels.shift();
            const newSeries = series.map((s, i) => {
                const key = Object.keys(datasets)[i];
                s.data.push(payload.reading[key] !== null && payload.reading[key] !== undefined ? payload.reading[key] : (s.data.length > 0 ? s.data[s.data.length-1] : 0));
                if (s.data.length > 50) s.data.shift();
                return s;
            });
            chart.updateOptions({xaxis:{categories:labels}});
            chart.updateSeries(newSeries);
            const tbody = document.querySelector('table tbody');
            const tr = document.createElement('tr');
            tr.innerHTML = `<td style="color:var(--primary);font-weight:600">Just now</td><td>${payload.reading.soil_moisture?payload.reading.soil_moisture+'%':'—'}</td><td>${payload.reading.temperature?payload.reading.temperature+'°C':'—'}</td><td>${payload.reading.humidity?payload.reading.humidity+'%':'—'}</td><td>${payload.reading.water_level?payload.reading.water_level+'%':'—'}</td><td>${payload.reading.light_intensity?payload.reading.light_intensity+' lux':'—'}</td><td>${payload.reading.ph_level??'—'}</td>`;
            tbody.insertBefore(tr, tbody.firstChild);
        }
    });
});
</script>
@endsection
