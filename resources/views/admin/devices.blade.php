@extends('layouts.app')
@section('title', 'Device Management')
@section('breadcrumb', 'Administration / Devices')

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

    /* Device Stats */
    .dev-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem; }
    .dev-stat {
        background: #fff; border: 1px solid var(--border-color); border-radius: 12px;
        padding: 1.25rem; position: relative; overflow: hidden;
    }
    .dev-stat::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; }
    .dev-stat.total::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
    .dev-stat.online::before { background: linear-gradient(90deg, #22c55e, #4ade80); }
    .dev-stat.offline::before { background: linear-gradient(90deg, #ef4444, #f87171); }
    .dev-stat.maint::before { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .dev-stat.battery::before { background: linear-gradient(90deg, #a855f7, #c084fc); }
    .dev-stat-label { font-size: 0.7rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .dev-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-main); }

    /* Toolbar */
    .dev-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
    .dev-filter-group { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .dev-filter-btn {
        padding: 0.45rem 0.85rem; background: #fff; border: 1px solid var(--border-color);
        border-radius: 8px; font-size: 0.8rem; font-weight: 600; color: var(--text-muted);
        cursor: pointer; transition: all 0.2s; text-decoration: none;
    }
    .dev-filter-btn:hover, .dev-filter-btn.active { border-color: var(--primary); color: var(--primary); }

    /* Device Grid */
    .dev-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; }
    .dev-card {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        padding: 1.25rem; border-top: 3px solid; transition: all 0.25s; position: relative;
    }
    .dev-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); }
    .dev-card.online { border-top-color: #22c55e; }
    .dev-card.offline { border-top-color: #ef4444; }
    .dev-card.maintenance { border-top-color: #f59e0b; }
    .dev-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .dev-name { font-size: 0.9rem; font-weight: 700; color: var(--text-main); }
    .dev-id { font-size: 0.7rem; color: var(--text-muted); font-family: 'SF Mono', 'Consolas', monospace; margin-top: 0.15rem; }
    .dev-status-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }
    .dev-status-dot.online { background: #22c55e; box-shadow: 0 0 8px rgba(34,197,94,0.5); animation: pulse-glow 2s infinite; }
    .dev-status-dot.offline { background: #ef4444; box-shadow: 0 0 8px rgba(239,68,68,0.4); }
    .dev-status-dot.maintenance { background: #f59e0b; box-shadow: 0 0 8px rgba(245,158,11,0.4); }
    @keyframes pulse-glow { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
    .dev-meta { margin-bottom: 0.5rem; }
    .dev-meta-row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem; }
    .dev-meta-row i { width: 14px; text-align: center; color: #94a3b8; font-size: 0.75rem; }
    .dev-meta-row strong { color: var(--text-main); font-weight: 600; }
    .dev-battery-bar {
        height: 6px; border-radius: 4px; background: #f1f5f9; overflow: hidden; margin-top: 0.75rem;
    }
    .dev-battery-fill { height: 100%; border-radius: 4px; transition: width 0.5s ease; }
    .dev-battery-info { display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem; font-size: 0.75rem; }
    .dev-footer {
        font-size: 0.75rem; color: var(--text-muted); padding-top: 0.75rem; margin-top: 0.75rem;
        border-top: 1px solid #f1f5f9;
    }

    /* Type Distribution */
    .type-dist-wrap {
        background: #fff; border: 1px solid var(--border-color); border-radius: 14px;
        padding: 1.25rem; margin-bottom: 1.5rem;
    }
    .type-dist-title { font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; }
    .type-chips { display: flex; gap: 0.5rem; flex-wrap: wrap; }
    .type-chip {
        display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem;
        background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 8px;
        font-size: 0.8rem; font-weight: 600; color: var(--text-main); transition: all 0.2s;
    }
    .type-chip .chip-count { font-weight: 800; color: var(--primary); }

    .pagination-wrap { margin-top: 1.5rem; }
    @media (max-width: 1200px) { .dev-grid { grid-template-columns: repeat(3, 1fr); } .dev-stats { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 768px) { .dev-grid { grid-template-columns: repeat(2, 1fr); } .admin-header { flex-direction: column; gap: 1rem; } .dev-stats { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 480px) { .dev-grid { grid-template-columns: 1fr; } }
</style>

<!-- Header -->
<div class="admin-header">
    <div>
        <h1 class="admin-title">Device Oversight</h1>
        <p class="admin-subtitle">Monitor and manage all IoT sensors across the platform</p>
    </div>
    <div class="admin-tabs">
        <a href="{{ route('admin.index') }}" class="admin-tab"><i class="fas fa-th-large"></i> Overview</a>
        <a href="{{ route('admin.users') }}" class="admin-tab"><i class="fas fa-users"></i> Users</a>
        <a href="{{ route('admin.devices') }}" class="admin-tab active"><i class="fas fa-microchip"></i> Devices</a>
        <a href="{{ route('admin.alerts') }}" class="admin-tab"><i class="fas fa-bell"></i> Alerts</a>
        <a href="{{ route('admin.activity') }}" class="admin-tab"><i class="fas fa-history"></i> Activity</a>
    </div>
</div>

<!-- Stats -->
<div class="dev-stats">
    <div class="dev-stat total">
        <div class="dev-stat-label">Total Devices</div>
        <div class="dev-stat-value">{{ $deviceStats['total'] }}</div>
    </div>
    <div class="dev-stat online">
        <div class="dev-stat-label">Online</div>
        <div class="dev-stat-value" style="color: #22c55e;">{{ $deviceStats['online'] }}</div>
    </div>
    <div class="dev-stat offline">
        <div class="dev-stat-label">Offline</div>
        <div class="dev-stat-value" style="color: #ef4444;">{{ $deviceStats['offline'] }}</div>
    </div>
    <div class="dev-stat maint">
        <div class="dev-stat-label">Maintenance</div>
        <div class="dev-stat-value" style="color: #f59e0b;">{{ $deviceStats['maintenance'] }}</div>
    </div>
    <div class="dev-stat battery">
        <div class="dev-stat-label">Low Battery</div>
        <div class="dev-stat-value" style="color: #a855f7;">{{ $deviceStats['low_battery'] }}</div>
    </div>
</div>

<!-- Type Distribution -->
@if(!empty($sensorTypes))
<div class="type-dist-wrap">
    <div class="type-dist-title"><i class="fas fa-layer-group" style="color: var(--primary); margin-right: 0.5rem;"></i>Sensor Types</div>
    <div class="type-chips">
        @foreach($sensorTypes as $type => $count)
        <div class="type-chip">
            <i class="fas fa-circle" style="font-size: 0.5rem; color: var(--primary);"></i>
            {{ ucfirst(str_replace('_', ' ', $type)) }}
            <span class="chip-count">{{ $count }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Toolbar -->
<div class="dev-toolbar">
    <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; width: 100%; justify-content: space-between;">
        <div class="dev-filter-group">
            <a href="{{ route('admin.devices') }}" class="dev-filter-btn {{ !request('status') ? 'active' : '' }}">All</a>
            <a href="{{ route('admin.devices', ['status' => 'online']) }}" class="dev-filter-btn {{ request('status') === 'online' ? 'active' : '' }}">
                <i class="fas fa-circle" style="font-size: 0.5rem; color: #22c55e;"></i> Online
            </a>
            <a href="{{ route('admin.devices', ['status' => 'offline']) }}" class="dev-filter-btn {{ request('status') === 'offline' ? 'active' : '' }}">
                <i class="fas fa-circle" style="font-size: 0.5rem; color: #ef4444;"></i> Offline
            </a>
            <a href="{{ route('admin.devices', ['status' => 'maintenance']) }}" class="dev-filter-btn {{ request('status') === 'maintenance' ? 'active' : '' }}">
                <i class="fas fa-circle" style="font-size: 0.5rem; color: #f59e0b;"></i> Maintenance
            </a>
        </div>
        <button onclick="document.getElementById('add-device-modal').classList.remove('hidden')" class="dev-filter-btn active" style="background: var(--primary); color: white; border-color: var(--primary);">
            <i class="fas fa-plus"></i> Register Device
        </button>
    </div>
</div>

<!-- Device Grid -->
<div class="dev-grid">
    @forelse($sensors as $sensor)
    <div class="dev-card {{ $sensor->status }}">
        <div class="dev-header">
            <div>
                <div class="dev-name">{{ $sensor->name }}</div>
                <div class="dev-id">{{ $sensor->device_id }}</div>
            </div>
            <div style="display: flex; gap: 0.5rem; align-items: center;">
                <button type="button" onclick="editDevice({{ $sensor->id }}, '{{ addslashes($sensor->device_id) }}', '{{ addslashes($sensor->name) }}', '{{ $sensor->type }}', '{{ $sensor->status }}', '{{ $sensor->farm_id }}')" style="background:none; border:none; color: var(--text-muted); cursor:pointer; font-size: 0.85rem;" title="Edit Device"><i class="fas fa-edit"></i></button>
                <form method="POST" action="{{ route('admin.devices.delete', $sensor) }}" onsubmit="return confirm('Delete {{ $sensor->name }}?')" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="background:none; border:none; color: #ef4444; cursor:pointer; font-size: 0.85rem;" title="Delete Device"><i class="fas fa-trash-alt"></i></button>
                </form>
                <div class="dev-status-dot {{ $sensor->status }}" style="margin-left: 0.25rem;"></div>
            </div>
        </div>
        <div class="dev-meta">
            <div class="dev-meta-row"><i class="fas fa-seedling"></i> <strong>{{ $sensor->farm->name ?? 'Unassigned' }}</strong></div>
            <div class="dev-meta-row"><i class="fas fa-layer-group"></i> {{ ucfirst(str_replace('_', ' ', $sensor->type)) }}</div>
            @if($sensor->field)
            <div class="dev-meta-row"><i class="fas fa-map-pin"></i> {{ $sensor->field->name }}</div>
            @endif
        </div>
        @php $batteryLevel = $sensor->battery_level ?? 0; @endphp
        <div class="dev-battery-bar">
            <div class="dev-battery-fill" style="width: {{ $batteryLevel }}%; background: {{ $batteryLevel > 50 ? '#22c55e' : ($batteryLevel > 20 ? '#f59e0b' : '#ef4444') }};"></div>
        </div>
        <div class="dev-battery-info">
            <span style="color: {{ $batteryLevel > 50 ? '#22c55e' : ($batteryLevel > 20 ? '#f59e0b' : '#ef4444') }}; font-weight: 700;">
                <i class="fas fa-battery-{{ $batteryLevel > 75 ? 'full' : ($batteryLevel > 50 ? 'three-quarters' : ($batteryLevel > 25 ? 'half' : ($batteryLevel > 10 ? 'quarter' : 'empty'))) }}"></i>
                {{ $batteryLevel }}%
            </span>
            <span style="color: var(--text-muted);">Battery</span>
        </div>
        <div class="dev-footer">
            <i class="fas fa-clock" style="margin-right: 0.25rem;"></i>
            {{ $sensor->last_reading_at ? $sensor->last_reading_at->diffForHumans() : 'No readings' }}
        </div>
    </div>
    @empty
    <div style="grid-column: 1/-1; text-align: center; padding: 4rem 2rem; color: var(--text-muted);">
        <i class="fas fa-microchip" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
        <p style="font-size: 1rem; font-weight: 700; margin-bottom: 0.25rem;">No devices found</p>
        <p style="font-size: 0.85rem;">Sensors will appear here once registered.</p>
    </div>
    @endforelse
</div>

<div class="pagination-wrap">
    {{ $sensors->appends(request()->query())->links() }}
</div>

<style>
.dm-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:1rem}
.dm-modal{background:#fff;border:1px solid var(--border-color);border-radius:16px;padding:1.5rem;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.1)}
.dm-modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}
.dm-modal-header h3{font-size:1.1rem;font-weight:700;color:var(--text-main);margin:0}
.dm-modal-close{background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.2rem;transition:color .2s}
.dm-modal-close:hover{color:#ef4444}
.dm-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem}
.dm-input{width:100%;padding:.65rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;margin-bottom:1rem}
.dm-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.dm-actions{display:flex;gap:.75rem;padding-top:.75rem}
.dm-btn-cancel{flex:1;padding:.65rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;color:var(--text-muted)}
.dm-btn-submit{flex:1;padding:.65rem;background:var(--primary);color:white;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer}
.hidden{display:none !important}
</style>

<!-- Add Device Modal -->
<div id="add-device-modal" class="dm-modal-bg hidden">
    <div class="dm-modal">
        <div class="dm-modal-header">
            <h3>Register New Device</h3>
            <button onclick="document.getElementById('add-device-modal').classList.add('hidden')" class="dm-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" action="{{ route('admin.devices.store') }}">
            @csrf
            <label class="dm-label">Device ID</label>
            <input name="device_id" required class="dm-input" placeholder="e.g. SN-2026-X1" style="font-family:monospace;">
            <label class="dm-label">Display Name</label>
            <input name="name" required class="dm-input" placeholder="e.g. Weather Station A">
            <label class="dm-label">Hardware Type</label>
            <select name="type" class="dm-input">
                <option value="soil_moisture">Soil Moisture Node</option>
                <option value="weather_station">Weather Telemetry</option>
                <option value="water_level">Water Level Sensor</option>
                <option value="camera">Optical Node</option>
            </select>
            <label class="dm-label">Farm Assignment (Optional)</label>
            <input name="farm_id" class="dm-input" placeholder="Farm ID (Leave empty for unassigned)">
            <div class="dm-actions">
                <button type="button" onclick="document.getElementById('add-device-modal').classList.add('hidden')" class="dm-btn-cancel">Cancel</button>
                <button type="submit" class="dm-btn-submit">Register Device</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Device Modal -->
<div id="edit-device-modal" class="dm-modal-bg hidden">
    <div class="dm-modal">
        <div class="dm-modal-header">
            <h3>Edit Device</h3>
            <button onclick="document.getElementById('edit-device-modal').classList.add('hidden')" class="dm-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <form id="edit-device-form" method="POST" action="">
            @csrf
            @method('PUT')
            <label class="dm-label">Device ID</label>
            <input name="device_id" id="edit-dev-id" required class="dm-input" style="font-family:monospace;">
            <label class="dm-label">Display Name</label>
            <input name="name" id="edit-dev-name" required class="dm-input">
            <label class="dm-label">Hardware Type</label>
            <select name="type" id="edit-dev-type" class="dm-input">
                <option value="soil_moisture">Soil Moisture Node</option>
                <option value="weather_station">Weather Telemetry</option>
                <option value="water_level">Water Level Sensor</option>
                <option value="camera">Optical Node</option>
            </select>
            <label class="dm-label">Status</label>
            <select name="status" id="edit-dev-status" class="dm-input">
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="maintenance">Maintenance</option>
            </select>
            <label class="dm-label">Farm Assignment (Optional)</label>
            <input name="farm_id" id="edit-dev-farm" class="dm-input" placeholder="Farm ID">
            <div class="dm-actions">
                <button type="button" onclick="document.getElementById('edit-device-modal').classList.add('hidden')" class="dm-btn-cancel">Cancel</button>
                <button type="submit" class="dm-btn-submit">Update Device</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editDevice(id, deviceId, name, type, status, farmId) {
        const form = document.getElementById('edit-device-form');
        form.action = `/admin-panel/devices/${id}`;
        document.getElementById('edit-dev-id').value = deviceId;
        document.getElementById('edit-dev-name').value = name;
        document.getElementById('edit-dev-type').value = type;
        document.getElementById('edit-dev-status').value = status;
        document.getElementById('edit-dev-farm').value = farmId || '';
        document.getElementById('edit-device-modal').classList.remove('hidden');
    }
</script>
@endsection
