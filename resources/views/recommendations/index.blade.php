@extends('layouts.app')
@section('title', 'Crop Insights')
@section('breadcrumb', 'Intelligence / Crop Insights')

@section('content')
<style>
/* ── Layout ──────────────────────────────────────────── */
.rec-header{display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem}
.rec-summary{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap}
.rec-icon-wrap{width:52px;height:52px;border-radius:13px;background:rgba(33,150,83,.1);border:1px solid rgba(33,150,83,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0}

/* ── Priority strip ──────────────────────────────────── */
.pri-strip{display:flex;gap:.75rem;margin-bottom:1.5rem;flex-wrap:wrap}
.pri-chip{display:flex;align-items:center;gap:.4rem;padding:.35rem .85rem;border-radius:999px;font-size:.75rem;font-weight:700;border:1px solid;cursor:pointer;transition:all .2s}
.pri-chip.all{background:#f8fafc;border-color:var(--border-color);color:var(--text-muted)}
.pri-chip.high{background:#fee2e2;border-color:#fca5a5;color:#dc2626}
.pri-chip.medium{background:#fef3c7;border-color:#fde68a;color:#d97706}
.pri-chip.low{background:#dbeafe;border-color:#bfdbfe;color:#2563eb}
.pri-chip.active{box-shadow:0 0 0 2px currentColor}

/* ── Cards ───────────────────────────────────────────── */
.rec-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;margin-bottom:1.5rem}
.rec-card{background:#fff;border:1px solid var(--border-color);border-radius:13px;padding:1.35rem;transition:all .2s;display:flex;flex-direction:column;position:relative;overflow:hidden}
.rec-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.05)}
.rec-card-accent{position:absolute;top:0;left:0;width:100%;height:3px;border-radius:13px 13px 0 0}
.acc-high{background:linear-gradient(90deg,#ef4444,#f97316)}
.acc-medium{background:linear-gradient(90deg,#f59e0b,#eab308)}
.acc-low{background:linear-gradient(90deg,#3b82f6,#06b6d4)}

.rec-cat-icon{width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.05rem}
/* category colours */
.cat-irrigation{background:#dbeafe;color:#3b82f6}
.cat-weather{background:#fef3c7;color:#f59e0b}
.cat-soil{background:#ffedd5;color:#f97316}
.cat-crop{background:#dcfce7;color:#22c55e}
.cat-sensor{background:#f3e8ff;color:#a855f7}
.cat-alert{background:#fee2e2;color:#ef4444}
.cat-fertilizer{background:#fce7f3;color:#ec4899}

.pri-badge{font-size:.6rem;font-weight:700;padding:.18rem .55rem;border-radius:999px;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap}
.pri-high{background:#fee2e2;color:#dc2626}
.pri-medium{background:#fef3c7;color:#d97706}
.pri-low{background:#dbeafe;color:#2563eb}

.conf-bar{width:48px;height:4px;background:#e2e8f0;border-radius:99px;overflow:hidden}
.conf-fill{height:100%;background:var(--primary);border-radius:99px}

.rec-action{display:inline-flex;align-items:center;gap:.4rem;font-size:.78rem;font-weight:700;color:var(--primary);text-decoration:none;padding:.3rem .7rem;border:1px solid rgba(33,150,83,.3);border-radius:6px;background:rgba(33,150,83,.05);transition:all .2s;margin-top:.75rem;align-self:flex-start}
.rec-action:hover{background:rgba(33,150,83,.12);border-color:var(--primary)}

.rec-note{background:var(--bg-body);border:1px solid var(--border-color);border-radius:12px;padding:1.1rem 1.25rem;display:flex;align-items:flex-start;gap:.85rem}
@media(max-width:768px){.rec-grid{grid-template-columns:1fr}}
</style>

{{-- ── Header ──────────────────────────────────────────── --}}
<div class="rec-header">
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0">Crop Insights</h1>
        <p style="color:var(--text-muted);font-size:.875rem;margin-top:.25rem">
            Data-driven recommendations from sensors, weather, and crop health.
        </p>
    </div>
    @if($farms->count() > 1)
    <form method="GET" action="{{ route('recommendations.index') }}">
        <select name="farm_id" onchange="this.form.submit()"
            style="padding:.5rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.85rem;background:#fff;color:var(--text-main);outline:none">
            @foreach($farms as $farm)
            <option value="{{ $farm->id }}" {{ $activeFarm?->id == $farm->id ? 'selected' : '' }}>{{ $farm->name }}</option>
            @endforeach
        </select>
    </form>
    @endif
</div>

@if($activeFarm)

{{-- ── Summary bar ─────────────────────────────────────── --}}
@php
    $highCount   = $recommendations->where('priority','high')->count();
    $medCount    = $recommendations->where('priority','medium')->count();
    $lowCount    = $recommendations->where('priority','low')->count();
    $cropCount   = $activeFarm->fields()->withCount(['crops' => fn($q) => $q->where('status','growing')])->get()->sum('crops_count');
@endphp
<div class="rec-summary">
    <div class="rec-icon-wrap"><i class="fas fa-leaf" style="font-size:1.4rem;color:var(--primary)"></i></div>
    <div style="flex:1">
        <h3 style="font-size:1rem;font-weight:700;color:var(--text-main);margin-bottom:.2rem">
            {{ $recommendations->count() }} Recommendation{{ $recommendations->count() !== 1 ? 's' : '' }}
            for <span style="color:var(--primary)">{{ $activeFarm->name }}</span>
        </h3>
        <p style="font-size:.8rem;color:var(--text-muted);margin:0">
            @if($cropCount > 0)
                {{ $cropCount }} active crop{{ $cropCount !== 1 ? 's' : '' }} tracked ·
            @endif
            Based on live sensors, weather &amp; crop data
            @if($highCount > 0)
                · <span style="color:#dc2626;font-weight:700">{{ $highCount }} urgent</span>
            @endif
        </p>
    </div>
    <a href="{{ route('farms.show', $activeFarm) }}"
        style="font-size:.8rem;color:var(--primary);font-weight:600;text-decoration:none;white-space:nowrap">
        View Farm →
    </a>
</div>

{{-- ── Priority filter chips ───────────────────────────── --}}
<div class="pri-strip">
    <span class="pri-chip all active" onclick="filterRecs('all',this)">All ({{ $recommendations->count() }})</span>
    @if($highCount > 0)
    <span class="pri-chip high" onclick="filterRecs('high',this)">🔴 Urgent ({{ $highCount }})</span>
    @endif
    @if($medCount > 0)
    <span class="pri-chip medium" onclick="filterRecs('medium',this)">🟡 Watch ({{ $medCount }})</span>
    @endif
    @if($lowCount > 0)
    <span class="pri-chip low" onclick="filterRecs('low',this)">🔵 Info ({{ $lowCount }})</span>
    @endif
</div>

{{-- ── Cards grid ──────────────────────────────────────── --}}
<div class="rec-grid" id="rec-grid">
@foreach($recommendations as $rec)
@php
    $catIconMap = [
        'irrigation' => 'fa-tint',
        'weather'    => 'fa-cloud-sun',
        'soil'       => 'fa-layer-group',
        'crop'       => 'fa-seedling',
        'sensor'     => 'fa-wifi',
        'alert'      => 'fa-bell',
        'fertilizer' => 'fa-flask',
    ];
    $faIcon = 'fa-' . ($rec['icon'] ?? 'lightbulb');
@endphp
<div class="rec-card" data-priority="{{ $rec['priority'] }}">
    <div class="rec-card-accent acc-{{ $rec['priority'] }}"></div>

    <div style="display:flex;align-items:flex-start;gap:.9rem">
        <div class="rec-cat-icon cat-{{ $rec['category'] }}">
            <i class="fas {{ $faIcon }}"></i>
        </div>
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.4rem;flex-wrap:wrap">
                <span style="font-size:.9rem;font-weight:700;color:var(--text-main);line-height:1.3">{{ $rec['title'] }}</span>
                <span class="pri-badge pri-{{ $rec['priority'] }}">{{ ucfirst($rec['priority']) }}</span>
            </div>
            <p style="font-size:.83rem;color:var(--text-muted);line-height:1.65;margin:0">{{ $rec['description'] }}</p>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:1rem;padding-top:.85rem;border-top:1px solid var(--border-color)">
        <div style="display:flex;align-items:center;gap:.75rem">
            <span style="font-size:.62rem;font-weight:700;text-transform:uppercase;padding:.2rem .5rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:5px;color:var(--text-muted)">
                {{ ucfirst($rec['category']) }}
            </span>
            <div style="display:flex;align-items:center;gap:.4rem">
                <div class="conf-bar"><div class="conf-fill" style="width:{{ $rec['confidence'] }}%"></div></div>
                <span style="font-size:.75rem;font-family:monospace;font-weight:600;color:var(--primary)">{{ $rec['confidence'] }}%</span>
            </div>
        </div>
        @if(!empty($rec['action']) && !empty($rec['action_url']))
        <a href="{{ $rec['action_url'] }}" class="rec-action">
            {{ $rec['action'] }} <i class="fas fa-arrow-right" style="font-size:.65rem"></i>
        </a>
        @endif
    </div>
</div>
@endforeach
</div>

{{-- ── Footer note ─────────────────────────────────────── --}}
<div class="rec-note">
    <i class="fas fa-info-circle" style="color:#94a3b8;font-size:1rem;margin-top:.1rem;flex-shrink:0"></i>
    <div>
        <p style="font-size:.8rem;font-weight:700;color:var(--text-main);margin:0 0 .2rem">About These Insights</p>
        <p style="font-size:.78rem;color:var(--text-muted);line-height:1.6;margin:0">
            Generated from live soil sensors, location-specific weather, crop health scores, irrigation history,
            and crop-specific thresholds. Confidence reflects data completeness.
            Always consult a local agronomist before major decisions.
        </p>
    </div>
</div>

@else
<div style="text-align:center;padding:4rem;background:#fff;border:2px dashed var(--border-color);border-radius:12px">
    <i class="fas fa-seedling" style="font-size:2.5rem;color:#cbd5e1;margin-bottom:1rem;display:block"></i>
    <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:.5rem">No farm selected</h3>
    <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:1.5rem">Add a farm and connect sensors to receive data-driven insights.</p>
    <a href="{{ route('farms.index') }}" style="display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.25rem;background:var(--primary);color:#fff;border-radius:8px;font-weight:700;font-size:.875rem;text-decoration:none">
        <i class="fas fa-plus"></i> Add Farm
    </a>
</div>
@endif

@section('scripts')
<script>
function filterRecs(priority, chip) {
    // Update chip styles
    document.querySelectorAll('.pri-chip').forEach(c => c.classList.remove('active'));
    chip.classList.add('active');

    // Show/hide cards
    document.querySelectorAll('#rec-grid .rec-card').forEach(card => {
        card.style.display = (priority === 'all' || card.dataset.priority === priority) ? '' : 'none';
    });
}
</script>
@endsection
@endsection
