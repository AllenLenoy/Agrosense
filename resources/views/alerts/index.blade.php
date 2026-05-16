@extends('layouts.app')
@section('title', 'Alerts')
@section('breadcrumb', 'Monitoring / Alerts')

@section('content')
<style>
    .alert-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .alert-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .alert-stat { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; }
    .alert-stat-label { font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .alert-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }
    .alert-filters { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
    .alert-select { padding: 0.6rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.85rem; color: var(--text-main); background: var(--bg-body); outline: none; }
    .alert-row { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; margin-bottom: 0.75rem; display: flex; gap: 1rem; transition: all 0.2s; }
    .alert-row:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    .alert-row.unread { border-left: 3px solid; }
    .alert-row.unread.critical { border-left-color: #ef4444; background: #fef2f2; }
    .alert-row.unread.warning { border-left-color: #f59e0b; background: #fffbeb; }
    .alert-row.unread.info { border-left-color: #3b82f6; background: #eff6ff; }
    .alert-row.unread.success { border-left-color: #22c55e; background: #f0fdf4; }
    .alert-icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .icon-critical { background: #fee2e2; color: #ef4444; }
    .icon-warning { background: #fef3c7; color: #f59e0b; }
    .icon-success { background: #dcfce7; color: #22c55e; }
    .icon-info { background: #dbeafe; color: #3b82f6; }
    .alert-body { flex: 1; min-width: 0; }
    .alert-title-row { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem; flex-wrap: wrap; }
    .alert-title { font-size: 0.95rem; font-weight: 700; color: var(--text-main); }
    .tag-new { background: #dbeafe; color: #1e40af; font-size: 0.6rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .tag-resolved { background: #dcfce7; color: #166534; font-size: 0.6rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .alert-msg { font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; }
    .alert-meta { display: flex; align-items: center; gap: 1rem; margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.75rem; color: var(--text-muted); flex-wrap: wrap; }
    .alert-meta i { width: 14px; color: #94a3b8; }
    .alert-actions { display: flex; flex-direction: column; gap: 0.5rem; flex-shrink: 0; }
    .btn-mark-read { padding: 0.4rem 0.75rem; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
    .btn-mark-read:hover { border-color: var(--primary); color: var(--primary); }
    .btn-resolve { padding: 0.4rem 0.75rem; background: #dcfce7; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 0.75rem; font-weight: 700; color: #166534; cursor: pointer; text-transform: uppercase; letter-spacing: 0.3px; transition: all 0.2s; }
    .btn-resolve:hover { background: #bbf7d0; }
    .btn-mark-all { padding: 0.5rem 1rem; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted); cursor: pointer; transition: all 0.2s; }
    .btn-mark-all:hover { border-color: var(--primary); color: var(--primary); }
    .empty-alerts { text-align: center; padding: 4rem; background: #fff; border: 2px dashed var(--border-color); border-radius: 12px; }
    .empty-alerts i { font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem; }
    @media (max-width: 768px) { .alert-stats { grid-template-columns: repeat(2, 1fr); } .alert-row { flex-direction: column; } .alert-actions { flex-direction: row; } }
</style>

<!-- Header -->
<div class="alert-header">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">System Alerts</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Monitor {{ $stats['total'] }} notifications across your agricultural infrastructure.</p>
    </div>
    @if($stats['unread'] > 0)
    <form method="POST" action="{{ route('alerts.readAll') }}">
        @csrf
        <button class="btn-mark-all"><i class="fas fa-check-double"></i> Mark All Read</button>
    </form>
    @endif
</div>

<!-- Stats -->
<div class="alert-stats">
    <div class="alert-stat">
        <div class="alert-stat-label">Total Alerts</div>
        <div class="alert-stat-value">{{ $stats['total'] }}</div>
    </div>
    <div class="alert-stat">
        <div class="alert-stat-label">Unread</div>
        <div class="alert-stat-value" style="color: {{ $stats['unread'] > 0 ? '#f59e0b' : 'var(--text-main)' }}">{{ $stats['unread'] }}</div>
    </div>
    <div class="alert-stat">
        <div class="alert-stat-label">Critical</div>
        <div class="alert-stat-value" style="color: {{ $stats['critical'] > 0 ? '#ef4444' : 'var(--text-main)' }}">{{ $stats['critical'] }}</div>
    </div>
    <div class="alert-stat">
        <div class="alert-stat-label">Warnings</div>
        <div class="alert-stat-value" style="color: {{ $stats['warnings'] > 0 ? '#f59e0b' : 'var(--text-main)' }}">{{ $stats['warnings'] }}</div>
    </div>
</div>

<!-- Filters -->
<div class="alert-filters">
    <form method="GET" style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
        <select name="type" class="alert-select" onchange="this.form.submit()">
            <option value="">All Priorities</option>
            <option value="critical" {{ request('type') === 'critical' ? 'selected' : '' }}>Critical</option>
            <option value="warning" {{ request('type') === 'warning' ? 'selected' : '' }}>Warning</option>
            <option value="info" {{ request('type') === 'info' ? 'selected' : '' }}>Info</option>
            <option value="success" {{ request('type') === 'success' ? 'selected' : '' }}>Success</option>
        </select>
        <select name="category" class="alert-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <option value="moisture" {{ request('category') === 'moisture' ? 'selected' : '' }}>Moisture Level</option>
            <option value="temperature" {{ request('category') === 'temperature' ? 'selected' : '' }}>Temperature</option>
            <option value="device" {{ request('category') === 'device' ? 'selected' : '' }}>Device Status</option>
            <option value="weather" {{ request('category') === 'weather' ? 'selected' : '' }}>Weather Event</option>
            <option value="irrigation" {{ request('category') === 'irrigation' ? 'selected' : '' }}>Irrigation Cycle</option>
        </select>
    </form>
</div>

<!-- Alert List -->
@forelse($alerts as $alert)
<div class="alert-row {{ !$alert->is_read ? 'unread ' . $alert->type : '' }}">
    <div class="alert-icon-box icon-{{ $alert->type }}">
        @switch($alert->type)
            @case('critical')<i class="fas fa-exclamation-circle"></i>@break
            @case('warning')<i class="fas fa-exclamation-triangle"></i>@break
            @case('success')<i class="fas fa-check-circle"></i>@break
            @default<i class="fas fa-info-circle"></i>
        @endswitch
    </div>
    
    <div class="alert-body">
        <div class="alert-title-row">
            <span class="alert-title">{{ $alert->title }}</span>
            @if(!$alert->is_read)<span class="tag-new">New</span>@endif
            @if($alert->is_resolved)<span class="tag-resolved">Resolved</span>@endif
        </div>
        <p class="alert-msg">{{ $alert->message }}</p>
        <div class="alert-meta">
            <span><i class="far fa-clock"></i> {{ $alert->created_at->diffForHumans() }}</span>
            <span><i class="fas fa-tag"></i> {{ ucfirst($alert->category) }}</span>
            @if($alert->sensor)<span><i class="fas fa-microchip"></i> {{ $alert->sensor->name }}</span>@endif
        </div>
    </div>
    
    <div class="alert-actions">
        @if(!$alert->is_read)
        <form method="POST" action="{{ route('alerts.read', $alert) }}">
            @csrf
            <button class="btn-mark-read"><i class="fas fa-check"></i> Read</button>
        </form>
        @endif
        @if(!$alert->is_resolved && in_array($alert->type, ['critical', 'warning']))
        <form method="POST" action="{{ route('alerts.resolve', $alert) }}">
            @csrf
            <button class="btn-resolve">Resolve</button>
        </form>
        @endif
    </div>
</div>
@empty
<div class="empty-alerts">
    <i class="fas fa-bell-slash"></i>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No active alerts</h3>
    <p style="font-size: 0.9rem; color: var(--text-muted);">All systems are running normally. We'll notify you if anything needs attention.</p>
</div>
@endforelse

<div style="margin-top: 1.5rem;">{{ $alerts->links() }}</div>
@endsection
