@extends('layouts.app')
@section('title', 'Alert Management')
@section('breadcrumb', 'Administration / Alerts')

@section('content')
<style>
    .admin-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
    .admin-title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0; }
    .admin-subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem; }
    .admin-tabs { display: flex; gap: 0.5rem; }
    .admin-tab {
        padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;
        text-decoration: none; color: var(--text-muted); background: var(--bg-body);
        border: 1px solid var(--border-color); transition: all 0.2s;
    }
    .admin-tab:hover { border-color: var(--primary); color: var(--primary); }
    .admin-tab.active { background: var(--primary); color: white; border-color: var(--primary); }

    /* Alert Stats */
    .alert-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .alert-stat {
        background: #fff; border: 1px solid var(--border-color); border-radius: 12px;
        padding: 1.25rem; position: relative; overflow: hidden; text-align: center;
    }
    .alert-stat::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .alert-stat.total::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
    .alert-stat.unresolved::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .alert-stat.critical::before { background: linear-gradient(90deg, #ef4444, #f87171); }
    .alert-stat.warning::before { background: linear-gradient(90deg, #f97316, #fb923c); }
    .alert-stat.info-stat::before { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .alert-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }
    .alert-stat-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0.25rem; }

    /* Toolbar */
    .alert-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
    .alert-filter-group { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .alert-filter-btn {
        padding: 0.45rem 0.85rem; background: #fff; border: 1px solid var(--border-color);
        border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted);
        cursor: pointer; transition: all 0.2s; text-decoration: none;
    }
    .alert-filter-btn:hover, .alert-filter-btn.active { border-color: var(--primary); color: var(--primary); }

    /* Alert Table */
    .alert-table-wrap {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden;
    }
    .alert-table { width: 100%; border-collapse: collapse; }
    .alert-table th {
        padding: 0.75rem 1.25rem; text-align: left; font-size: 0.7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted);
        background: #fafbfc; border-bottom: 1px solid var(--border-color);
    }
    .alert-table td {
        padding: 0.85rem 1.25rem; font-size: 0.85rem; color: var(--text-main);
        border-bottom: 1px solid #f1f5f9; vertical-align: middle;
    }
    .alert-table tr:last-child td { border-bottom: none; }
    .alert-table tr:hover td { background: #fafbfc; }
    .alert-type-badge {
        font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 0.35rem;
    }
    .alert-type-badge.critical { background: #fee2e2; color: #dc2626; }
    .alert-type-badge.warning { background: #fef3c7; color: #d97706; }
    .alert-type-badge.info { background: #dbeafe; color: #2563eb; }
    .alert-status-badge {
        font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px;
        text-transform: uppercase;
    }
    .alert-status-badge.resolved { background: #dcfce7; color: #166534; }
    .alert-status-badge.unresolved { background: #fee2e2; color: #dc2626; }
    .alert-title-cell { font-weight: 700; }
    .alert-msg { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.15rem; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .alert-farm-link { font-size: 0.8rem; color: var(--primary); font-weight: 600; text-decoration: none; }
    .alert-farm-link:hover { text-decoration: underline; }

    .pagination-wrap { padding: 1rem 1.25rem; border-top: 1px solid var(--border-color); }
    @media (max-width: 1200px) { .alert-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .admin-header { flex-direction: column; gap: 1rem; } .alert-stats { grid-template-columns: repeat(2, 1fr); } }
</style>

<!-- Header -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">Alert Management</h1>
        <p class="admin-subtitle">Monitor and resolve system-wide alerts and notifications</p>
    </div>
    <div class="admin-tabs">
        <a href="{{ route('admin.index') }}" class="admin-tab"><i class="fas fa-th-large"></i> Overview</a>
        <a href="{{ route('admin.users') }}" class="admin-tab"><i class="fas fa-users"></i> Users</a>
        <a href="{{ route('admin.devices') }}" class="admin-tab"><i class="fas fa-microchip"></i> Devices</a>
        <a href="{{ route('admin.alerts') }}" class="admin-tab active"><i class="fas fa-bell"></i> Alerts</a>
        <a href="{{ route('admin.activity') }}" class="admin-tab"><i class="fas fa-history"></i> Activity</a>
    </div>
</div>

<!-- Stats -->
<div class="alert-stats">
    <div class="alert-stat total">
        <div class="alert-stat-value">{{ $alertStats['total'] }}</div>
        <div class="alert-stat-label">Total Alerts</div>
    </div>
    <div class="alert-stat unresolved">
        <div class="alert-stat-value" style="color: #f59e0b;">{{ $alertStats['unresolved'] }}</div>
        <div class="alert-stat-label">Unresolved</div>
    </div>
    <div class="alert-stat critical">
        <div class="alert-stat-value" style="color: #ef4444;">{{ $alertStats['critical'] }}</div>
        <div class="alert-stat-label">Critical</div>
    </div>
    <div class="alert-stat warning">
        <div class="alert-stat-value" style="color: #f97316;">{{ $alertStats['warning'] }}</div>
        <div class="alert-stat-label">Warnings</div>
    </div>
    <div class="alert-stat info-stat">
        <div class="alert-stat-value" style="color: #3b82f6;">{{ $alertStats['info'] }}</div>
        <div class="alert-stat-label">Informational</div>
    </div>
</div>

<!-- Toolbar -->
<div class="alert-toolbar">
    <div class="alert-filter-group">
        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); padding: 0.45rem 0;">Type:</span>
        <a href="{{ route('admin.alerts') }}" class="alert-filter-btn {{ !request('type') && !request('status') ? 'active' : '' }}">All</a>
        <a href="{{ route('admin.alerts', ['type' => 'critical']) }}" class="alert-filter-btn {{ request('type') === 'critical' ? 'active' : '' }}">
            <i class="fas fa-circle" style="font-size: 0.5rem; color: #ef4444;"></i> Critical
        </a>
        <a href="{{ route('admin.alerts', ['type' => 'warning']) }}" class="alert-filter-btn {{ request('type') === 'warning' ? 'active' : '' }}">
            <i class="fas fa-circle" style="font-size: 0.5rem; color: #f59e0b;"></i> Warning
        </a>
        <a href="{{ route('admin.alerts', ['type' => 'info']) }}" class="alert-filter-btn {{ request('type') === 'info' ? 'active' : '' }}">
            <i class="fas fa-circle" style="font-size: 0.5rem; color: #3b82f6;"></i> Info
        </a>
    </div>
    <div class="alert-filter-group">
        <span style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); padding: 0.45rem 0;">Status:</span>
        <a href="{{ route('admin.alerts', array_merge(request()->except('status'), ['status' => 'unresolved'])) }}" class="alert-filter-btn {{ request('status') === 'unresolved' ? 'active' : '' }}">Unresolved</a>
        <a href="{{ route('admin.alerts', array_merge(request()->except('status'), ['status' => 'resolved'])) }}" class="alert-filter-btn {{ request('status') === 'resolved' ? 'active' : '' }}">Resolved</a>
    </div>
</div>

<!-- Alert Table -->
<div class="alert-table-wrap">
    <div style="overflow-x: auto;">
        <table class="alert-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Alert</th>
                    <th>Farm</th>
                    <th>Sensor</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alerts as $alert)
                <tr>
                    <td>
                        <span class="alert-type-badge {{ $alert->type }}">
                            <i class="fas {{ $alert->type === 'critical' ? 'fa-exclamation-circle' : ($alert->type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle') }}"></i>
                            {{ ucfirst($alert->type) }}
                        </span>
                    </td>
                    <td>
                        <div class="alert-title-cell">{{ $alert->title }}</div>
                        <div class="alert-msg">{{ $alert->message }}</div>
                    </td>
                    <td>
                        @if($alert->farm)
                        <span class="alert-farm-link">{{ $alert->farm->name }}</span>
                        @else
                        <span style="color: var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem;">
                        {{ $alert->sensor->name ?? '—' }}
                    </td>
                    <td>
                        <span class="alert-status-badge {{ $alert->is_resolved ? 'resolved' : 'unresolved' }}">
                            {{ $alert->is_resolved ? 'Resolved' : 'Unresolved' }}
                        </span>
                    </td>
                    <td style="color: var(--text-muted); font-size: 0.8rem; white-space: nowrap;">
                        {{ $alert->created_at->format('M d, H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-muted);">
                        <i class="fas fa-check-circle" style="font-size: 2rem; color: #22c55e; display: block; margin-bottom: 0.75rem;"></i>
                        <span style="font-weight: 600;">No alerts match your filters</span>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $alerts->appends(request()->query())->links() }}
    </div>
</div>
@endsection
