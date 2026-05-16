@extends('layouts.app')
@section('title', 'Activity Log')
@section('breadcrumb', 'Administration / Activity')

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

    /* Activity Timeline */
    .activity-wrap {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px; overflow: hidden;
    }
    .activity-header {
        padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);
        display: flex; justify-content: space-between; align-items: center;
    }
    .activity-header h3 { font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin: 0; }
    .activity-list { padding: 0; }
    .act-item {
        display: flex; align-items: flex-start; gap: 1rem; padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9; transition: background 0.15s;
    }
    .act-item:last-child { border-bottom: none; }
    .act-item:hover { background: #fafbfc; }

    .act-timeline {
        display: flex; flex-direction: column; align-items: center; padding-top: 4px; flex-shrink: 0;
    }
    .act-dot {
        width: 10px; height: 10px; border-radius: 50%; border: 2px solid;
        flex-shrink: 0;
    }
    .act-dot.create { border-color: #22c55e; background: #dcfce7; }
    .act-dot.update { border-color: #3b82f6; background: #dbeafe; }
    .act-dot.delete { border-color: #ef4444; background: #fee2e2; }
    .act-dot.login { border-color: #6366f1; background: #e0e7ff; }
    .act-dot.default { border-color: #94a3b8; background: #f1f5f9; }

    .act-content { flex: 1; min-width: 0; }
    .act-text { font-size: 0.85rem; color: var(--text-main); line-height: 1.5; }
    .act-text strong { font-weight: 700; }
    .act-meta { display: flex; gap: 1rem; margin-top: 0.35rem; font-size: 0.75rem; color: var(--text-muted); }
    .act-meta i { width: 12px; text-align: center; }
    .act-action-badge {
        font-size: 0.6rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 6px;
        text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap;
    }
    .act-action-badge.create { background: #dcfce7; color: #166534; }
    .act-action-badge.update { background: #dbeafe; color: #1d4ed8; }
    .act-action-badge.delete { background: #fee2e2; color: #dc2626; }
    .act-action-badge.login { background: #e0e7ff; color: #4338ca; }
    .act-action-badge.default { background: #f1f5f9; color: #64748b; }

    .act-time {
        font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; padding-top: 2px;
        min-width: 90px; text-align: right;
    }

    .act-avatar {
        width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
        border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08); flex-shrink: 0;
    }

    .pagination-wrap { padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); }

    .empty-state {
        text-align: center; padding: 4rem 2rem; color: var(--text-muted);
    }
    .empty-state i { font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem; display: block; }

    @media (max-width: 768px) { .admin-header { flex-direction: column; gap: 1rem; } }
</style>

<!-- Header -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">Activity Log</h1>
        <p class="admin-subtitle">Full audit trail of platform activity and system events</p>
    </div>
    <div class="admin-tabs">
        <a href="{{ route('admin.index') }}" class="admin-tab"><i class="fas fa-th-large"></i> Overview</a>
        <a href="{{ route('admin.users') }}" class="admin-tab"><i class="fas fa-users"></i> Users</a>
        <a href="{{ route('admin.devices') }}" class="admin-tab"><i class="fas fa-microchip"></i> Devices</a>
        <a href="{{ route('admin.alerts') }}" class="admin-tab"><i class="fas fa-bell"></i> Alerts</a>
        <a href="{{ route('admin.activity') }}" class="admin-tab active"><i class="fas fa-history"></i> Activity</a>
    </div>
</div>

<!-- Activity Timeline -->
<div class="activity-wrap">
    <div class="activity-header">
        <h3><i class="fas fa-stream" style="color: #6366f1; margin-right: 0.5rem;"></i> System Activity Timeline</h3>
        <span style="font-size: 0.8rem; color: var(--text-muted);">{{ $logs->total() }} total events</span>
    </div>
    <div class="activity-list">
        @forelse($logs as $log)
        @php
            $actionType = 'default';
            $action = strtolower($log->action ?? '');
            if (str_contains($action, 'create') || str_contains($action, 'register') || str_contains($action, 'add')) $actionType = 'create';
            elseif (str_contains($action, 'update') || str_contains($action, 'edit') || str_contains($action, 'change')) $actionType = 'update';
            elseif (str_contains($action, 'delete') || str_contains($action, 'remove')) $actionType = 'delete';
            elseif (str_contains($action, 'login') || str_contains($action, 'auth') || str_contains($action, 'logout')) $actionType = 'login';
        @endphp
        <div class="act-item">
            @if($log->user)
            <img src="{{ $log->user->avatar_url }}" alt="{{ $log->user->name }}" class="act-avatar">
            @else
            <div class="act-avatar" style="background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 0.8rem;">
                <i class="fas fa-robot"></i>
            </div>
            @endif
            <div class="act-timeline">
                <div class="act-dot {{ $actionType }}"></div>
            </div>
            <div class="act-content">
                <div class="act-text">
                    <strong>{{ $log->user->name ?? 'System' }}</strong>
                    {{ $log->description ?? $log->action }}
                    @if($log->subject_type)
                    <span style="color: var(--text-muted); font-size: 0.8rem;"> on {{ class_basename($log->subject_type) }}</span>
                    @endif
                </div>
                <div class="act-meta">
                    <span class="act-action-badge {{ $actionType }}">{{ $log->action ?? 'event' }}</span>
                    @if($log->ip_address)
                    <span><i class="fas fa-globe"></i> {{ $log->ip_address }}</span>
                    @endif
                </div>
            </div>
            <div class="act-time">
                {{ $log->created_at->diffForHumans() }}
            </div>
        </div>
        @empty
        <div class="empty-state">
            <i class="fas fa-stream"></i>
            <p style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">No activity recorded</p>
            <p style="font-size: 0.85rem;">Events will appear here as users interact with the platform.</p>
        </div>
        @endforelse
    </div>
    <div class="pagination-wrap">
        {{ $logs->links() }}
    </div>
</div>
@endsection
