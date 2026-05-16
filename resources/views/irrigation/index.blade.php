@extends('layouts.app')
@section('title', 'Irrigation')
@section('breadcrumb', 'Operations / Irrigation')

@section('content')
<style>
    .irr-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .irr-stat { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; }
    .irr-stat-label { font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .irr-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }
    .irr-stat-unit { font-size: 1rem; font-weight: 400; color: var(--text-muted); margin-left: 0.25rem; }
    .irr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }
    .irr-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .irr-card-title { font-size: 0.85rem; font-weight: 700; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.25rem; }
    .irr-card-desc { font-size: 0.8rem; color: var(--text-muted); margin-bottom: 1.25rem; }
    .irr-form-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
    .irr-select { flex: 1; padding: 0.65rem 1rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; color: var(--text-main); background: var(--bg-body); outline: none; transition: all 0.2s; }
    .irr-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33, 150, 83, 0.1); }
    .irr-btn-start { width: 100%; padding: 0.75rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; }
    .irr-btn-start:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(33,150,83,0.2); }
    .active-badge { display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 700; color: #0ea5e9; }
    .active-dot { width: 8px; height: 8px; border-radius: 50%; background: #0ea5e9; animation: pulse-soft 2s infinite; }
    @keyframes pulse-soft { 0%,100% { opacity:1; } 50% { opacity:0.4; } }
    .active-item { display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 10px; margin-bottom: 0.75rem; }
    .active-item-info h4 { font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem; }
    .active-item-info p { font-size: 0.75rem; color: var(--text-muted); }
    .btn-stop { padding: 0.4rem 1rem; background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; border-radius: 8px; font-size: 0.75rem; font-weight: 700; cursor: pointer; text-transform: uppercase; letter-spacing: 0.3px; transition: all 0.2s; }
    .btn-stop:hover { background: #fee2e2; }
    .irr-bottom { display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem; }
    .schedule-item { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 10px; padding: 1.25rem; margin-bottom: 0.75rem; }
    .schedule-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; }
    .schedule-name { font-size: 0.9rem; font-weight: 700; color: var(--text-main); }
    .schedule-badge { font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-active { background: #dcfce7; color: #166534; }
    .badge-paused { background: #f1f5f9; color: #64748b; }
    .schedule-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted); }
    .schedule-meta i { width: 16px; color: #94a3b8; }
    .smart-tag { display: inline-flex; align-items: center; gap: 0.35rem; background: #eff6ff; color: #3b82f6; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; margin-top: 0.5rem; }
    .irr-table { width: 100%; border-collapse: collapse; }
    .irr-table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); background: var(--bg-body); }
    .irr-table td { padding: 0.75rem 1rem; font-size: 0.85rem; color: var(--text-main); border-bottom: 1px solid var(--border-color); }
    .irr-table tr:hover td { background: #f8fafc; }
    .type-badge { font-size: 0.65rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .type-auto { background: #eff6ff; color: #3b82f6; }
    .type-scheduled { background: #dcfce7; color: #166534; }
    .type-manual { background: #fef3c7; color: #92400e; }
    .empty-state { text-align: center; padding: 3rem; color: var(--text-muted); }
    .empty-state i { font-size: 2rem; color: #cbd5e1; margin-bottom: 0.75rem; }
    @media (max-width: 1024px) { .irr-stats { grid-template-columns: repeat(3, 1fr); } .irr-grid, .irr-bottom { grid-template-columns: 1fr; } }
    @media (max-width: 640px) { .irr-stats { grid-template-columns: repeat(2, 1fr); } }
    .hidden { display: none !important; }
</style>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Irrigation Control</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Monitor and manage automated watering systems across your farms.</p>
    </div>
    <a href="{{ url()->previous() == url()->current() ? route('dashboard') : url()->previous() }}" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<!-- Stats -->
<div class="irr-stats">
    <div class="irr-stat">
        <div class="irr-stat-label">Active Now</div>
        <div class="irr-stat-value" style="color: {{ $stats['active_count'] > 0 ? '#0ea5e9' : 'var(--text-main)' }}">{{ $stats['active_count'] }}</div>
    </div>
    <div class="irr-stat">
        <div class="irr-stat-label">Water Today</div>
        <div class="irr-stat-value">{{ number_format($stats['water_today']) }}<span class="irr-stat-unit">L</span></div>
    </div>
    <div class="irr-stat">
        <div class="irr-stat-label">Water This Week</div>
        <div class="irr-stat-value">{{ number_format($stats['water_week']) }}<span class="irr-stat-unit">L</span></div>
    </div>
    <div class="irr-stat">
        <div class="irr-stat-label">Avg Duration</div>
        <div class="irr-stat-value">{{ $stats['avg_duration'] }}<span class="irr-stat-unit">min</span></div>
    </div>
    <div class="irr-stat">
        <div class="irr-stat-label">Schedules</div>
        <div class="irr-stat-value">{{ $stats['schedule_count'] }}</div>
    </div>
</div>

<!-- Quick Start + Active Sessions -->
<div class="irr-grid">
    <!-- Quick Start -->
    <div class="irr-card">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
            <i class="fas fa-play" style="color: var(--primary);"></i>
            <div class="irr-card-title">Quick Start Manual Flow</div>
        </div>
        <div class="irr-card-desc">Instantly trigger watering for a specific field.</div>
        
        <form method="POST" action="{{ route('irrigation.start') }}">
            @csrf
            <div class="irr-form-row">
                <select name="field_id" class="irr-select" required>
                    <option value="">Select Field...</option>
                    @php
                        $userFarmIds = auth()->user()->isAdmin()
                            ? \App\Models\Farm::pluck('id')
                            : auth()->user()->farms()->pluck('id');
                        $allFields = \App\Models\Field::whereIn('farm_id', $userFarmIds)->get();
                    @endphp
                    @foreach($allFields as $field)
                        <option value="{{ $field->id }}">{{ $field->name }}</option>
                    @endforeach
                </select>
                <select name="duration" class="irr-select" style="width: 120px; flex: none;">
                    <option value="15">15 mins</option>
                    <option value="30">30 mins</option>
                    <option value="60">60 mins</option>
                </select>
            </div>
            <button type="submit" class="irr-btn-start"><i class="fas fa-tint"></i> Start Irrigation Now</button>
        </form>
    </div>

    <!-- Active Sessions -->
    <div class="irr-card" style="border-color: {{ $activeLogs->count() > 0 ? '#bae6fd' : 'var(--border-color)' }};">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
            <div class="active-badge">
                @if($activeLogs->count() > 0) <div class="active-dot"></div> @endif
                Active Irrigation ({{ $activeLogs->count() }})
            </div>
        </div>
        
        @if($activeLogs->count() > 0)
            @foreach($activeLogs as $log)
            <div class="active-item">
                <div class="active-item-info">
                    <h4>{{ $log->field?->name ?? 'General' }}</h4>
                    <p><i class="fas fa-clock"></i> {{ (int) abs(now()->diffInMinutes($log->started_at)) }}m elapsed</p>
                </div>
                <form method="POST" action="{{ route('irrigation.toggle', $log) }}">
                    @csrf
                    <button type="submit" class="btn-stop" onclick="return confirm('Stop this irrigation session?')">
                        <i class="fas fa-stop"></i> Stop
                    </button>
                </form>
            </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="fas fa-tint-slash"></i>
                <p style="font-size: 0.85rem;">No active sessions right now.</p>
            </div>
        @endif
    </div>
</div>

<!-- Schedules + History -->
<div class="irr-bottom">
    <!-- Schedules -->
    <div class="irr-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div class="irr-card-title">Schedules</div>
        </div>
        @forelse($schedules as $schedule)
        <div class="schedule-item">
            <div class="schedule-header">
                <div class="schedule-name">{{ $schedule->name }}</div>
                <span class="schedule-badge {{ $schedule->is_active ? 'badge-active' : 'badge-paused' }}">{{ $schedule->is_active ? 'Active' : 'Paused' }}</span>
            </div>
            <div class="schedule-meta">
                <div><i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }}</div>
                <div><i class="fas fa-hourglass-half"></i> {{ $schedule->duration_minutes }}m</div>
                <div style="grid-column: span 2;"><i class="fas fa-calendar"></i> {{ implode(', ', array_map('ucfirst', $schedule->days_of_week)) }}</div>
            </div>
            @if($schedule->is_smart)
            <div class="smart-tag"><i class="fas fa-brain"></i> Smart ({{ $schedule->moisture_threshold }}%)</div>
            @endif
            <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted);">
                <span>{{ $schedule->farm->name }}</span>
                <span>{{ $schedule->field?->name ?? 'All fields' }}</span>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <p style="font-size: 0.85rem;">No schedules configured</p>
        </div>
        @endforelse
    </div>

    <!-- History Table -->
    <div class="irr-card">
        <div class="irr-card-title" style="margin-bottom: 1.25rem;">Irrigation History</div>
        <div style="overflow-x: auto;">
            <table class="irr-table">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Duration</th>
                        <th>Water Used</th>
                        <th>Reason</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogs as $log)
                    <tr>
                        <td style="font-weight: 700;">{{ $log->field?->name ?? 'General' }}</td>
                        <td>
                            <span class="type-badge {{ $log->type === 'automatic' ? 'type-auto' : ($log->type === 'scheduled' ? 'type-scheduled' : 'type-manual') }}">
                                {{ ucfirst($log->type) }}
                            </span>
                        </td>
                        <td>
                            <span class="type-badge {{ $log->status === 'completed' ? 'type-scheduled' : 'type-auto' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td style="color: var(--text-muted); font-family: monospace;">{{ $log->duration_minutes ? $log->duration_minutes . 'm' : '—' }}</td>
                        <td style="color: var(--text-muted); font-family: monospace;">{{ $log->water_used_liters ? number_format($log->water_used_liters) . 'L' : '—' }}</td>
                        <td style="color: var(--text-muted); font-size: 0.8rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $log->trigger_reason ?? '—' }}</td>
                        <td style="color: var(--text-muted); font-size: 0.8rem; font-family: monospace;">{{ $log->started_at->format('M d, H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="empty-state">No irrigation history found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1rem;">{{ $recentLogs->links() }}</div>
    </div>
</div>
@endsection
