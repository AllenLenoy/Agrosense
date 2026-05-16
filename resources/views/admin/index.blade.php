@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('breadcrumb', 'Administration / Overview')

@section('content')
<style>
    /* Admin Dashboard Styles */
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

    /* Metric Cards */
    .admin-metrics { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .admin-metric {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        padding: 1.25rem; position: relative; overflow: hidden; transition: all 0.25s;
    }
    .admin-metric:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .admin-metric::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
        border-radius: 14px 14px 0 0;
    }
    .admin-metric.green::before { background: linear-gradient(90deg, #22c55e, #16a34a); }
    .admin-metric.blue::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }
    .admin-metric.purple::before { background: linear-gradient(90deg, #a855f7, #9333ea); }
    .admin-metric.amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }
    .admin-metric.red::before { background: linear-gradient(90deg, #ef4444, #dc2626); }
    .metric-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .metric-icon-wrap {
        width: 38px; height: 38px; border-radius: 10px; display: flex;
        align-items: center; justify-content: center; font-size: 1rem;
    }
    .metric-icon-wrap.green { background: #dcfce7; color: #16a34a; }
    .metric-icon-wrap.blue { background: #dbeafe; color: #2563eb; }
    .metric-icon-wrap.purple { background: #f3e8ff; color: #9333ea; }
    .metric-icon-wrap.amber { background: #fef3c7; color: #d97706; }
    .metric-icon-wrap.red { background: #fee2e2; color: #dc2626; }
    .metric-change { font-size: 0.7rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 6px; }
    .metric-change.up { background: #dcfce7; color: #166534; }
    .metric-change.down { background: #fee2e2; color: #b91c1c; }
    .metric-change.neutral { background: #f1f5f9; color: #64748b; }
    .metric-value { font-size: 1.6rem; font-weight: 800; color: var(--text-main); line-height: 1; }
    .metric-label { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); margin-top: 0.35rem; text-transform: uppercase; letter-spacing: 0.3px; }

    /* Admin Grid Layout */
    .admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .admin-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }

    /* Admin Cards */
    .admin-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        overflow: hidden; transition: all 0.2s;
    }
    .admin-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
    .admin-card-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);
    }
    .admin-card-head h3 { font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin: 0; }
    .admin-card-head .view-link {
        font-size: 0.8rem; font-weight: 600; color: var(--primary);
        text-decoration: none; display: flex; align-items: center; gap: 0.35rem;
    }
    .admin-card-head .view-link:hover { opacity: 0.8; }
    .admin-card-body { padding: 1.5rem; }

    /* User Row */
    .admin-user-row {
        display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9; transition: background 0.15s;
    }
    .admin-user-row:last-child { border-bottom: none; }
    .admin-user-row:hover { background: #fafbfc; margin: 0 -1.5rem; padding: 0.75rem 1.5rem; }
    .admin-user-avatar {
        width: 36px; height: 36px; border-radius: 50%; object-fit: cover;
        border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .admin-user-info { flex: 1; min-width: 0; }
    .admin-user-name { font-size: 0.85rem; font-weight: 700; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .admin-user-email { font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .admin-badge {
        font-size: 0.6rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .admin-badge.role-admin { background: linear-gradient(135deg, #fee2e2, #fecdd3); color: #be123c; }
    .admin-badge.role-farmer { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }

    /* Farm Row */
    .admin-farm-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 0.75rem 0; border-bottom: 1px solid #f1f5f9;
    }
    .admin-farm-row:last-child { border-bottom: none; }
    .admin-farm-name { font-size: 0.85rem; font-weight: 700; color: var(--text-main); }
    .admin-farm-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.15rem; }
    .admin-farm-sensors {
        font-size: 0.8rem; font-weight: 700; color: var(--primary);
        background: var(--primary-light); padding: 0.3rem 0.75rem; border-radius: 8px;
    }

    /* Alert Row */
    .admin-alert-row {
        display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .admin-alert-row:last-child { border-bottom: none; }
    .admin-alert-icon {
        width: 32px; height: 32px; border-radius: 8px; display: flex;
        align-items: center; justify-content: center; font-size: 0.85rem; flex-shrink: 0;
    }
    .admin-alert-icon.critical { background: #fee2e2; color: #dc2626; }
    .admin-alert-icon.warning { background: #fef3c7; color: #d97706; }
    .admin-alert-icon.info { background: #dbeafe; color: #2563eb; }
    .admin-alert-title { font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.15rem; }
    .admin-alert-desc { font-size: 0.75rem; color: var(--text-muted); }
    .admin-alert-time { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; margin-left: auto; padding-left: 0.5rem; }

    /* Sensor Health Widget */
    .health-bar-wrap { display: flex; height: 12px; border-radius: 8px; overflow: hidden; margin: 1rem 0; background: #f1f5f9; }
    .health-segment { height: 100%; transition: width 0.5s ease; }
    .health-segment.online { background: linear-gradient(90deg, #22c55e, #4ade80); }
    .health-segment.offline { background: linear-gradient(90deg, #ef4444, #f87171); }
    .health-segment.maintenance { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .health-legend { display: flex; gap: 1.5rem; flex-wrap: wrap; }
    .health-legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted); }
    .health-dot { width: 10px; height: 10px; border-radius: 50%; }
    .health-dot.online { background: #22c55e; }
    .health-dot.offline { background: #ef4444; }
    .health-dot.maintenance { background: #f59e0b; }
    .health-dot.low-battery { background: #a855f7; }

    /* Activity Feed */
    .activity-item {
        display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.65rem 0;
        border-bottom: 1px solid #f1f5f9; font-size: 0.82rem;
    }
    .activity-item:last-child { border-bottom: none; }
    .activity-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); margin-top: 5px; flex-shrink: 0; }
    .activity-text { color: var(--text-muted); flex: 1; }
    .activity-text strong { color: var(--text-main); font-weight: 700; }
    .activity-time { font-size: 0.7rem; color: #94a3b8; white-space: nowrap; }

    /* Chart Container */
    .admin-chart-container { height: 220px; }

    /* Responsive */
    @media (max-width: 1200px) { .admin-metrics { grid-template-columns: repeat(3, 1fr); } .admin-grid-3 { grid-template-columns: 1fr 1fr; } }
    @media (max-width: 768px) { .admin-metrics { grid-template-columns: repeat(2, 1fr); } .admin-grid, .admin-grid-3 { grid-template-columns: 1fr; } .admin-header { flex-direction: column; gap: 1rem; } }
</style>

<!-- Header -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">System Administration</h1>
        <p class="admin-subtitle">Platform-wide overview and management console</p>
    </div>
    <div class="admin-tabs">
        <a href="{{ route('admin.index') }}" class="admin-tab active">
            <i class="fas fa-th-large"></i> Overview
        </a>
        <a href="{{ route('admin.users') }}" class="admin-tab">
            <i class="fas fa-users"></i> Users
        </a>
        <a href="{{ route('admin.devices') }}" class="admin-tab">
            <i class="fas fa-microchip"></i> Devices
        </a>
        <a href="{{ route('admin.alerts') }}" class="admin-tab">
            <i class="fas fa-bell"></i> Alerts
        </a>
        <a href="{{ route('admin.activity') }}" class="admin-tab">
            <i class="fas fa-history"></i> Activity
        </a>
    </div>
</div>

<!-- Metric Cards -->
<div class="admin-metrics">
    <div class="admin-metric green">
        <div class="metric-top">
            <div class="metric-icon-wrap green"><i class="fas fa-users"></i></div>
            @if($stats['new_users_week'] > 0)
            <span class="metric-change up">+{{ $stats['new_users_week'] }} this week</span>
            @else
            <span class="metric-change neutral">No change</span>
            @endif
        </div>
        <div class="metric-value">{{ $stats['total_users'] }}</div>
        <div class="metric-label">Total Users</div>
    </div>
    <div class="admin-metric blue">
        <div class="metric-top">
            <div class="metric-icon-wrap blue"><i class="fas fa-map-marked-alt"></i></div>
            @if($stats['new_farms_week'] > 0)
            <span class="metric-change up">+{{ $stats['new_farms_week'] }} this week</span>
            @else
            <span class="metric-change neutral">Stable</span>
            @endif
        </div>
        <div class="metric-value">{{ $stats['total_farms'] }}</div>
        <div class="metric-label">Total Farms</div>
    </div>
    <div class="admin-metric purple">
        <div class="metric-top">
            <div class="metric-icon-wrap purple"><i class="fas fa-microchip"></i></div>
            <span class="metric-change up">{{ $stats['active_sensors'] }} active</span>
        </div>
        <div class="metric-value">{{ $stats['total_sensors'] }}</div>
        <div class="metric-label">IoT Sensors</div>
    </div>
    <div class="admin-metric amber">
        <div class="metric-top">
            <div class="metric-icon-wrap amber"><i class="fas fa-tint"></i></div>
            <span class="metric-change neutral">Today</span>
        </div>
        <div class="metric-value">{{ $stats['irrigation_today'] }}</div>
        <div class="metric-label">Irrigation Runs</div>
    </div>
    <div class="admin-metric red">
        <div class="metric-top">
            <div class="metric-icon-wrap red"><i class="fas fa-exclamation-triangle"></i></div>
            @if($stats['critical_alerts'] > 0)
            <span class="metric-change down">{{ $stats['critical_alerts'] }} critical</span>
            @else
            <span class="metric-change up">All clear</span>
            @endif
        </div>
        <div class="metric-value">{{ $stats['unresolved_alerts'] }}</div>
        <div class="metric-label">Open Alerts</div>
    </div>
</div>

<!-- Main Dashboard Grid -->
<div class="admin-grid">
    <!-- Sensor Health -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3><i class="fas fa-heartbeat" style="color: #22c55e; margin-right: 0.5rem;"></i>Sensor Health</h3>
            <a href="{{ route('admin.devices') }}" class="view-link">Manage <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="admin-card-body">
            @php
                $totalSensors = max($sensorHealth['online'] + $sensorHealth['offline'] + $sensorHealth['maintenance'], 1);
                $onlinePct = round($sensorHealth['online'] / $totalSensors * 100);
                $offlinePct = round($sensorHealth['offline'] / $totalSensors * 100);
                $maintPct = 100 - $onlinePct - $offlinePct;
            @endphp
            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.5rem;">
                <span style="font-size: 2rem; font-weight: 800; color: var(--text-main);">{{ $onlinePct }}%</span>
                <span style="font-size: 0.85rem; font-weight: 600; color: #22c55e;">Online</span>
            </div>
            <div class="health-bar-wrap">
                <div class="health-segment online" style="width: {{ $onlinePct }}%;"></div>
                <div class="health-segment offline" style="width: {{ $offlinePct }}%;"></div>
                <div class="health-segment maintenance" style="width: {{ $maintPct }}%;"></div>
            </div>
            <div class="health-legend">
                <div class="health-legend-item"><div class="health-dot online"></div> Online ({{ $sensorHealth['online'] }})</div>
                <div class="health-legend-item"><div class="health-dot offline"></div> Offline ({{ $sensorHealth['offline'] }})</div>
                <div class="health-legend-item"><div class="health-dot maintenance"></div> Maintenance ({{ $sensorHealth['maintenance'] }})</div>
                <div class="health-legend-item"><div class="health-dot low-battery"></div> Low Battery ({{ $sensorHealth['low_battery'] }})</div>
            </div>
        </div>
    </div>

    <!-- User Registration Trend -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3><i class="fas fa-chart-line" style="color: #3b82f6; margin-right: 0.5rem;"></i>User Registrations (7 Days)</h3>
            <a href="{{ route('admin.users') }}" class="view-link">All Users <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="admin-card-body">
            <div id="userTrendChart" class="admin-chart-container"></div>
        </div>
    </div>
</div>

<!-- Three-Column Grid -->
<div class="admin-grid-3">
    <!-- Recent Users -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3><i class="fas fa-user-plus" style="color: #22c55e; margin-right: 0.5rem;"></i>Recent Users</h3>
            <a href="{{ route('admin.users') }}" class="view-link">View all <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="admin-card-body">
            @forelse($recentUsers as $user)
            <div class="admin-user-row">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="admin-user-avatar">
                <div class="admin-user-info">
                    <div class="admin-user-name">{{ $user->name }}</div>
                    <div class="admin-user-email">{{ $user->email }}</div>
                </div>
                <span class="admin-badge role-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
            </div>
            @empty
            <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                <i class="fas fa-users" style="font-size: 1.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                No users yet
            </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Farms -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3><i class="fas fa-seedling" style="color: #16a34a; margin-right: 0.5rem;"></i>Recent Farms</h3>
            <span style="font-size: 0.8rem; color: var(--text-muted);">{{ number_format($stats['total_area']) }} acres total</span>
        </div>
        <div class="admin-card-body">
            @forelse($farms as $farm)
            <div class="admin-farm-row">
                <div>
                    <div class="admin-farm-name">{{ $farm->name }}</div>
                    <div class="admin-farm-meta">
                        <i class="fas fa-map-marker-alt" style="font-size: 0.7rem;"></i> {{ $farm->location }} · {{ $farm->owner->name ?? 'Unknown' }}
                    </div>
                </div>
                <span class="admin-farm-sensors">{{ $farm->sensors_count }} <i class="fas fa-microchip" style="font-size: 0.7rem;"></i></span>
            </div>
            @empty
            <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.85rem;">
                <i class="fas fa-seedling" style="font-size: 1.5rem; color: #cbd5e1; display: block; margin-bottom: 0.5rem;"></i>
                No farms registered
            </div>
            @endforelse
        </div>
    </div>

    <!-- Active Alerts -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3><i class="fas fa-bell" style="color: #f59e0b; margin-right: 0.5rem;"></i>Active Alerts</h3>
            <a href="{{ route('admin.alerts') }}" class="view-link">View all <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="admin-card-body">
            @forelse($recentAlerts as $alert)
            <div class="admin-alert-row">
                <div class="admin-alert-icon {{ $alert->type }}">
                    <i class="fas {{ $alert->type === 'critical' ? 'fa-exclamation-circle' : ($alert->type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle') }}"></i>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div class="admin-alert-title">{{ $alert->title }}</div>
                    <div class="admin-alert-desc">{{ Str::limit($alert->message, 50) }}</div>
                </div>
                <div class="admin-alert-time">{{ $alert->created_at->diffForHumans() }}</div>
            </div>
            @empty
            <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #22c55e; margin-bottom: 0.5rem; display: block;"></i>
                <span style="font-size: 0.85rem; font-weight: 600;">All clear — no active alerts</span>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Activity Feed -->
<div class="admin-card">
    <div class="admin-card-head">
        <h3><i class="fas fa-stream" style="color: #6366f1; margin-right: 0.5rem;"></i>Recent Activity</h3>
        <a href="{{ route('admin.activity') }}" class="view-link">Full log <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="admin-card-body">
        @forelse($recentActivity as $log)
        <div class="activity-item">
            <div class="activity-dot"></div>
            <div class="activity-text">
                <strong>{{ $log->user->name ?? 'System' }}</strong>
                {{ $log->description ?? $log->action }}
            </div>
            <div class="activity-time">{{ $log->created_at->diffForHumans() }}</div>
        </div>
        @empty
        <div style="text-align: center; padding: 1.5rem; color: var(--text-muted); font-size: 0.85rem;">
            No recent activity
        </div>
        @endforelse
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Registration Trend Chart
    var trendOptions = {
        series: [{
            name: 'New Users',
            data: {!! json_encode(array_column($userTrend, 'count')) !!}
        }],
        chart: {
            height: 220,
            type: 'area',
            fontFamily: 'Inter, sans-serif',
            toolbar: { show: false },
            zoom: { enabled: false },
            sparkline: { enabled: false }
        },
        colors: ['#3b82f6'],
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 90, 100] }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: {
            categories: {!! json_encode(array_column($userTrend, 'label')) !!},
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } }
        },
        yaxis: {
            labels: { style: { colors: '#64748b', fontSize: '11px', fontWeight: 500 } },
            min: 0
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
            strokeColors: '#3b82f6',
            strokeWidth: 2,
            hover: { size: 7 }
        },
        tooltip: { theme: 'light' }
    };

    var trendChart = new ApexCharts(document.querySelector("#userTrendChart"), trendOptions);
    trendChart.render();
});
</script>
@endsection
