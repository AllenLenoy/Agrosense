@extends('layouts.app')
@section('title', 'AI Insights')
@section('breadcrumb', 'Intelligence / AI Insights')

@section('content')
<style>
.rec-summary{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap}
.rec-robot{width:56px;height:56px;border-radius:14px;background:rgba(33,150,83,.1);border:1px solid rgba(33,150,83,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rec-robot i{font-size:1.5rem;color:var(--primary)}
.rec-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.5rem}
.rec-card{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.5rem;transition:all .2s}
.rec-card:hover{transform:translateY(-3px);box-shadow:0 8px 20px rgba(0,0,0,.04)}
.rec-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.rec-icon.crop{background:#dcfce7;color:#22c55e}.rec-icon.fertilizer{background:#f3e8ff;color:#a855f7}
.rec-icon.irrigation{background:#dbeafe;color:#3b82f6}.rec-icon.disease{background:#fee2e2;color:#ef4444}
.rec-icon.weather{background:#fef3c7;color:#f59e0b}.rec-icon.soil{background:#ffedd5;color:#f97316}
.pri-badge{font-size:.6rem;font-weight:700;padding:.15rem .5rem;border-radius:999px;text-transform:uppercase;letter-spacing:.5px}
.pri-high{background:#fee2e2;color:#dc2626}.pri-medium{background:#fef3c7;color:#d97706}.pri-low{background:#dbeafe;color:#2563eb}
.conf-bar{width:50px;height:4px;background:#e2e8f0;border-radius:99px;overflow:hidden}
.conf-fill{height:100%;background:var(--primary);border-radius:99px}
.rec-note{background:var(--bg-body);border:1px solid var(--border-color);border-radius:12px;padding:1.25rem;display:flex;align-items:flex-start;gap:1rem}
@media(max-width:768px){.rec-grid{grid-template-columns:1fr}}
</style>

<div style="margin-bottom:1.5rem">
    <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0">AI-Powered Recommendations</h1>
    <p style="color:var(--text-muted);font-size:.9rem;margin-top:.25rem">Smart suggestions based on your farm data, weather patterns, and crop health.</p>
</div>

@if($activeFarm)
<div class="rec-summary">
    <div class="rec-robot"><i class="fas fa-robot"></i></div>
    <div style="flex:1">
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:.25rem">{{ $recommendations->count() }} Active Recommendations</h3>
        <p style="font-size:.85rem;color:var(--text-muted)">Analysis based on <strong>{{ $activeFarm->name }}</strong> sensor data and regional weather patterns.</p>
    </div>
</div>

<div class="rec-grid">
@foreach($recommendations as $rec)
@php
    $icons = ['crop'=>'fa-seedling','fertilizer'=>'fa-flask','irrigation'=>'fa-tint','disease'=>'fa-shield-alt','weather'=>'fa-cloud-sun','soil'=>'fa-layer-group'];
    $icon = $icons[$rec['category']] ?? 'fa-lightbulb';
@endphp
<div class="rec-card">
    <div style="display:flex;align-items:flex-start;gap:1rem">
        <div class="rec-icon {{ $rec['category'] }}"><i class="fas {{ $icon }}"></i></div>
        <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.5rem;flex-wrap:wrap">
                <span style="font-size:.95rem;font-weight:700;color:var(--text-main)">{{ $rec['title'] }}</span>
                <span class="pri-badge pri-{{ $rec['priority'] }}">{{ ucfirst($rec['priority']) }}</span>
            </div>
            <p style="font-size:.85rem;color:var(--text-muted);line-height:1.6;margin-bottom:1rem">{{ $rec['description'] }}</p>
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:.75rem;border-top:1px solid var(--border-color)">
                <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;padding:.25rem .5rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:6px;color:var(--text-muted)">{{ ucfirst($rec['category']) }}</span>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;color:var(--text-muted)">Confidence</span>
                    <div class="conf-bar"><div class="conf-fill" style="width:{{ $rec['confidence'] }}%"></div></div>
                    <span style="font-size:.8rem;font-family:monospace;font-weight:600;color:var(--primary)">{{ $rec['confidence'] }}%</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>

<div class="rec-note">
    <i class="fas fa-info-circle" style="color:#94a3b8;font-size:1.1rem;margin-top:.1rem"></i>
    <div>
        <h4 style="font-size:.85rem;font-weight:700;color:var(--text-main);margin-bottom:.35rem">About AI Recommendations</h4>
        <p style="font-size:.8rem;color:var(--text-muted);line-height:1.6">Recommendations use soil sensor data, weather forecasts, crop health metrics, and regional databases. Always consult local experts before major decisions.</p>
    </div>
</div>
@else
<div style="text-align:center;padding:4rem;background:#fff;border:2px dashed var(--border-color);border-radius:12px">
    <i class="fas fa-brain" style="font-size:2.5rem;color:#cbd5e1;margin-bottom:1rem"></i>
    <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:.5rem">No recommendations available</h3>
    <p style="font-size:.9rem;color:var(--text-muted)">Add a farm and connect sensors to receive AI-powered insights.</p>
</div>
@endif
@endsection
