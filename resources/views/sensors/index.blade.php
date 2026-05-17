@extends('layouts.app')
@section('title', 'IoT Sensors')
@section('breadcrumb', 'Monitoring / IoT Sensors')

@section('content')
<style>
/* ── Layout ──────────────────────────────────────────── */
.s-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem}
.s-filter{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1rem;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;align-items:center;gap:.75rem}
.s-search{position:relative;flex:1;min-width:200px}
.s-search i{position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:.8rem}
.s-input{width:100%;padding:.6rem .75rem .6rem 2.25rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none}
.s-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.s-select{padding:.6rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.85rem;color:var(--text-main);background:var(--bg-body);outline:none;min-width:140px}
.s-btn{padding:.6rem 1.25rem;background:var(--primary);color:#fff;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:.5rem}
.s-btn:hover{background:var(--primary-dark)}
.s-btn-outline{padding:.6rem 1.25rem;background:#fff;color:var(--text-main);border:1px solid var(--border-color);border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:.5rem;text-decoration:none}
.s-btn-outline:hover{border-color:var(--primary);color:var(--primary)}

/* ── Sensor cards ────────────────────────────────────── */
.s-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem}
.s-card{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.25rem;transition:all .25s;position:relative}
.s-card:hover{transform:translateY(-3px);box-shadow:0 10px 24px rgba(0,0,0,.06);border-color:var(--primary)}
.s-card-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem}
.s-card-info{display:flex;align-items:center;gap:.75rem}
.s-card-icon{width:40px;height:40px;border-radius:10px;background:var(--primary-light);display:flex;align-items:center;justify-content:center}
.s-card-icon i{font-size:1.1rem;color:var(--primary)}
.s-card-icon.offline{background:#fee2e2}
.s-card-icon.offline i{color:#ef4444}
.s-card-name{font-size:.9rem;font-weight:700;color:var(--text-main)}
.s-card-id{font-size:.7rem;color:var(--text-muted);font-family:monospace;margin-top:.1rem}
.s-dot{width:8px;height:8px;border-radius:50%}
.s-dot.online{background:#22c55e;box-shadow:0 0 8px rgba(34,197,94,.5)}
.s-dot.offline{background:#ef4444}
.s-meta{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-bottom:1rem}
.s-meta-box{background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;padding:.6rem .75rem}
.s-meta-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.2rem}
.s-meta-val{font-size:.8rem;font-weight:600;color:var(--text-main);text-transform:capitalize}
.batt-bar{flex:1;height:4px;background:#e2e8f0;border-radius:99px;overflow:hidden}
.batt-fill{height:100%;border-radius:99px}
.batt-good{background:#22c55e}.batt-low{background:#ef4444}
.s-footer{padding-top:.75rem;border-top:1px solid var(--border-color);display:flex;flex-direction:column;gap:.3rem}
.s-footer-row{display:flex;align-items:center;gap:.5rem;font-size:.75rem;color:var(--text-muted)}
.s-footer-row i{width:14px;color:#94a3b8}
.s-card-actions{display:flex;gap:.5rem;margin-top:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color)}
.s-act-btn{flex:1;padding:.4rem;border-radius:7px;font-size:.75rem;font-weight:700;cursor:pointer;border:1px solid var(--border-color);background:#fff;color:var(--text-muted);transition:all .2s;text-align:center}
.s-act-btn:hover{border-color:var(--primary);color:var(--primary)}
.s-act-btn.danger:hover{border-color:#ef4444;color:#ef4444}
.s-empty{grid-column:span 3;text-align:center;padding:3rem;background:#fff;border:2px dashed var(--border-color);border-radius:12px}

/* ── Catalog picker ──────────────────────────────────── */
.cat-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:.75rem;margin-bottom:1.25rem}
.cat-card{border:2px solid var(--border-color);border-radius:10px;padding:.9rem;cursor:pointer;transition:all .2s;background:#fff}
.cat-card:hover{border-color:var(--primary);background:rgba(33,150,83,.02)}
.cat-card.selected{border-color:var(--primary);background:rgba(33,150,83,.05)}
.cat-card-top{display:flex;align-items:center;gap:.6rem;margin-bottom:.4rem}
.cat-icon{width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0}
.cat-name{font-size:.82rem;font-weight:700;color:var(--text-main);line-height:1.2}
.cat-brand{font-size:.68rem;color:var(--text-muted)}
.cat-measures{font-size:.72rem;color:var(--text-muted);margin-top:.25rem}
.cat-range{font-size:.68rem;font-family:monospace;color:#64748b;margin-top:.15rem}

/* ── Modal ───────────────────────────────────────────── */
.s-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem;overflow-y:auto}
.s-modal{background:#fff;border-radius:16px;width:100%;max-width:680px;box-shadow:0 20px 60px rgba(0,0,0,.12);overflow:hidden;max-height:90vh;display:flex;flex-direction:column}
.s-modal-head{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;flex-shrink:0}
.s-modal-head h3{font-size:1rem;font-weight:700;color:var(--text-main);margin:0}
.s-modal-body{padding:1.5rem;overflow-y:auto}
.s-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem}
.s-field{width:100%;padding:.65rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;margin-bottom:1rem}
.s-field:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.s-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.s-info-box{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:.85rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#166534;line-height:1.5;display:none}
.hidden{display:none !important}
@media(max-width:1024px){.s-grid{grid-template-columns:repeat(2,1fr)}.s-empty{grid-column:span 2}.cat-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.s-grid{grid-template-columns:1fr}.s-empty{grid-column:span 1}.cat-grid{grid-template-columns:1fr}}
</style>

{{-- ── Header ──────────────────────────────────────────── --}}
<div class="s-header">
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0">IoT Sensors</h1>
        <p style="color:var(--text-muted);font-size:.9rem;margin-top:.25rem">{{ $sensors->total() }} device{{ $sensors->total() !== 1 ? 's' : '' }} registered across all farms</p>
    </div>
    <button onclick="openModal('addModal')" class="s-btn"><i class="fas fa-plus"></i> Add Sensor</button>
</div>

@if(session('success'))
<div style="background:#dcfce7;color:#166534;padding:.9rem 1.25rem;border-radius:8px;margin-bottom:1.25rem;border:1px solid #bbf7d0;font-size:.875rem;font-weight:600;display:flex;align-items:center;gap:.6rem">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- ── Filters ─────────────────────────────────────────── --}}
<div class="s-filter">
    <form method="GET" style="display:flex;flex-wrap:wrap;align-items:center;gap:.75rem;width:100%">
        <div class="s-search">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or ID..." class="s-input">
        </div>
        <select name="status" class="s-select" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="online"      {{ request('status') === 'online'      ? 'selected' : '' }}>Online</option>
            <option value="offline"     {{ request('status') === 'offline'     ? 'selected' : '' }}>Offline</option>
            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>
        <select name="type" class="s-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            @foreach(['soil_moisture'=>'Soil Moisture','temperature'=>'Temperature','humidity'=>'Humidity','water_level'=>'Water Level','ph_sensor'=>'pH Sensor','light'=>'Light','wind'=>'Wind','rainfall'=>'Rainfall','weather_station'=>'Weather Station'] as $val => $label)
            <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="s-btn">Filter</button>
        @if(request()->hasAny(['search','status','type']))
        <a href="{{ route('sensors.index') }}" class="s-btn-outline">Clear</a>
        @endif
    </form>
</div>

{{-- ── Sensor Cards ────────────────────────────────────── --}}
<div class="s-grid">
    @forelse($sensors as $sensor)
    <div class="s-card">
        <div class="s-card-top">
            <div class="s-card-info">
                <div class="s-card-icon {{ $sensor->status !== 'online' ? 'offline' : '' }}">
                    <i class="fas fa-microchip"></i>
                </div>
                <div>
                    <div class="s-card-name">{{ $sensor->name }}</div>
                    <div class="s-card-id">{{ $sensor->device_id }}</div>
                </div>
            </div>
            <div class="s-dot {{ $sensor->status === 'online' ? 'online' : 'offline' }}"></div>
        </div>

        <div class="s-meta">
            <div class="s-meta-box">
                <div class="s-meta-label">Type</div>
                <div class="s-meta-val">{{ str_replace('_',' ',$sensor->type) }}</div>
            </div>
            <div class="s-meta-box">
                <div class="s-meta-label">Battery</div>
                <div style="display:flex;align-items:center;gap:.5rem;margin-top:.15rem">
                    <div class="batt-bar">
                        <div class="batt-fill {{ ($sensor->battery_level ?? 0) > 20 ? 'batt-good' : 'batt-low' }}"
                            style="width:{{ $sensor->battery_level ?? 0 }}%"></div>
                    </div>
                    <span class="s-meta-val" style="font-family:monospace;font-size:.75rem">{{ number_format($sensor->battery_level ?? 0,1) }}%</span>
                </div>
            </div>
        </div>

        @if($sensor->model)
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.75rem;padding:.3rem .6rem;background:var(--bg-body);border-radius:6px;border:1px solid var(--border-color)">
            <i class="fas fa-tag" style="margin-right:.3rem"></i>{{ $sensor->model }}
        </div>
        @endif

        <div class="s-footer">
            <div class="s-footer-row" id="sensor-ping-{{ $sensor->id }}">
                <i class="fas fa-satellite-dish" style="{{ $sensor->latestReading ? 'color:#22c55e' : '' }}"></i>
                <span>{{ $sensor->latestReading ? 'Pinged ' . $sensor->last_reading_at?->diffForHumans() : 'Awaiting first transmission' }}</span>
            </div>
            <div class="s-footer-row" style="justify-content:space-between">
                <span><i class="fas fa-tractor"></i> {{ $sensor->farm->name }}</span>
                <span>{{ $sensor->field?->name ?? 'Unassigned' }}</span>
            </div>
        </div>

        <div class="s-card-actions">
            <a href="{{ route('sensors.show', $sensor) }}" class="s-act-btn">
                <i class="fas fa-chart-line"></i> View Data
            </a>
            <button onclick="openEditModal({{ $sensor->id }}, '{{ addslashes($sensor->name) }}', '{{ $sensor->type }}', '{{ $sensor->field_id }}', '{{ addslashes($sensor->model ?? '') }}')"
                class="s-act-btn"><i class="fas fa-edit"></i> Edit</button>
            <form method="POST" action="{{ route('sensors.destroy', $sensor) }}" style="flex:1"
                onsubmit="return confirm('Remove {{ addslashes($sensor->name) }}? This will also delete all its readings.')">
                @csrf @method('DELETE')
                <button type="submit" class="s-act-btn danger" style="width:100%">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="s-empty">
        <i class="fas fa-microchip" style="font-size:2.5rem;color:#cbd5e1;display:block;margin-bottom:1rem"></i>
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:.5rem">No sensors found</h3>
        <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:1.5rem">Adjust filters or add a new sensor below.</p>
        <button onclick="openModal('addModal')" class="s-btn">Add Sensor</button>
    </div>
    @endforelse
</div>

<div style="margin-top:1.5rem">{{ $sensors->links() }}</div>

{{-- ── ADD SENSOR MODAL ────────────────────────────────── --}}
<div id="addModal" class="s-modal-bg hidden">
<div class="s-modal">
    <div class="s-modal-head">
        <h3>Add Sensor</h3>
        <button onclick="closeModal('addModal')" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.1rem"><i class="fas fa-times"></i></button>
    </div>
    <div class="s-modal-body">

        {{-- Step 1: Catalog picker --}}
        <div id="step-catalog">
            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">
                Select a sensor from the catalog to auto-fill its details, or scroll down to enter manually.
            </p>
            <div class="cat-grid">
                @foreach($catalog as $item)
                <div class="cat-card" onclick="selectCatalog({{ json_encode($item) }}, this)">
                    <div class="cat-card-top">
                        <div class="cat-icon" style="background:{{ $item['color'] }}18;color:{{ $item['color'] }}">
                            <i class="fas fa-{{ $item['icon'] }}"></i>
                        </div>
                        <div>
                            <div class="cat-name">{{ $item['name'] }}</div>
                            <div class="cat-brand">{{ $item['brand'] }}</div>
                        </div>
                    </div>
                    <div class="cat-measures"><i class="fas fa-ruler" style="font-size:.6rem;margin-right:.3rem"></i>{{ $item['measures'] }}</div>
                    <div class="cat-range">{{ $item['range'] }}</div>
                </div>
                @endforeach
            </div>
            <div style="text-align:center;margin-bottom:1rem">
                <button type="button" onclick="skipCatalog()" style="background:none;border:none;color:var(--primary);font-size:.85rem;font-weight:600;cursor:pointer;text-decoration:underline">
                    Skip — enter details manually
                </button>
            </div>
        </div>

        {{-- Step 2: Form --}}
        <div id="step-form" class="hidden">
            <div id="catalog-info-box" class="s-info-box"></div>

            <form method="POST" action="{{ route('sensors.storeFromIndex') }}">
                @csrf
                <label class="s-label">Farm</label>
                <select name="farm_id" required class="s-field" id="add-farm-select" onchange="loadFields(this.value)">
                    <option value="">— Select Farm —</option>
                    @foreach($farms as $farm)
                    <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                    @endforeach
                </select>

                <label class="s-label">Sensor Model</label>
                <input type="text" name="model" id="add-model" class="s-field" placeholder="e.g. DHT22" readonly
                    style="background:#f8fafc;cursor:default">

                <label class="s-label">Display Name</label>
                <input type="text" name="name" id="add-name" required class="s-field" placeholder="e.g. North Field Moisture Probe">

                <div class="s-row">
                    <div>
                        <label class="s-label">Sensor Type</label>
                        <select name="type" id="add-type" required class="s-field">
                            @foreach(['soil_moisture'=>'Soil Moisture','temperature'=>'Temperature','humidity'=>'Humidity','water_level'=>'Water Level','ph_sensor'=>'pH Sensor','light'=>'Light Intensity','wind'=>'Wind Speed','rainfall'=>'Rainfall','weather_station'=>'Weather Station'] as $v => $l)
                            <option value="{{ $v }}">{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="s-label">Zone / Field (Optional)</label>
                        <select name="field_id" id="add-field" class="s-field">
                            <option value="">— Unassigned —</option>
                        </select>
                    </div>
                </div>

                <label class="s-label">Device ID / MAC Address</label>
                <input type="text" name="device_id" required class="s-field" placeholder="e.g. ESP32-A8F9"
                    style="font-family:monospace">

                <div style="display:flex;justify-content:space-between;align-items:center;padding-top:.75rem;border-top:1px solid var(--border-color)">
                    <button type="button" onclick="backToCatalog()" style="background:none;border:none;color:var(--text-muted);font-size:.85rem;font-weight:600;cursor:pointer">
                        ← Back to catalog
                    </button>
                    <button type="submit" class="s-btn">Complete Pairing</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

{{-- ── EDIT SENSOR MODAL ───────────────────────────────── --}}
<div id="editModal" class="s-modal-bg hidden">
<div class="s-modal" style="max-width:440px">
    <div class="s-modal-head">
        <h3>Edit Sensor</h3>
        <button onclick="closeModal('editModal')" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1.1rem"><i class="fas fa-times"></i></button>
    </div>
    <div class="s-modal-body">
        <form method="POST" id="edit-form" action="">
            @csrf @method('PUT')
            <label class="s-label">Display Name</label>
            <input type="text" name="name" id="edit-name" required class="s-field">

            <label class="s-label">Sensor Type</label>
            <select name="type" id="edit-type" required class="s-field">
                @foreach(['soil_moisture'=>'Soil Moisture','temperature'=>'Temperature','humidity'=>'Humidity','water_level'=>'Water Level','ph_sensor'=>'pH Sensor','light'=>'Light Intensity','wind'=>'Wind Speed','rainfall'=>'Rainfall','weather_station'=>'Weather Station'] as $v => $l)
                <option value="{{ $v }}">{{ $l }}</option>
                @endforeach
            </select>

            <label class="s-label">Model</label>
            <input type="text" name="model" id="edit-model" class="s-field" placeholder="e.g. DHT22">

            <div style="display:flex;justify-content:flex-end;gap:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color)">
                <button type="button" onclick="closeModal('editModal')" style="background:none;border:none;color:var(--text-muted);font-weight:600;cursor:pointer;font-size:.875rem">Cancel</button>
                <button type="submit" class="s-btn">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</div>

@section('scripts')
<script>
// ── Modal helpers ─────────────────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.remove('hidden'); }
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

// ── Catalog selection ─────────────────────────────────────────────────────────
function selectCatalog(item, el) {
    document.querySelectorAll('.cat-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');

    // Fill form fields
    document.getElementById('add-model').value = item.name;
    document.getElementById('add-type').value  = item.id === 'dht22' || item.id === 'dht11' ? 'humidity' : item.type;
    document.getElementById('add-name').value  = item.name;

    // Show info box
    const box = document.getElementById('catalog-info-box');
    box.innerHTML = `<strong>${item.name}</strong> · ${item.brand}<br>
        <span style="opacity:.8">Measures: ${item.measures} &nbsp;|&nbsp; Range: ${item.range}</span><br>
        <span style="opacity:.75">${item.description}</span>`;
    box.style.display = 'block';

    // Switch to form step
    document.getElementById('step-catalog').classList.add('hidden');
    document.getElementById('step-form').classList.remove('hidden');
}

function skipCatalog() {
    document.getElementById('step-catalog').classList.add('hidden');
    document.getElementById('step-form').classList.remove('hidden');
}

function backToCatalog() {
    document.getElementById('step-form').classList.add('hidden');
    document.getElementById('step-catalog').classList.remove('hidden');
}

// ── Load fields for selected farm ─────────────────────────────────────────────
const farmFields = @json($farms->mapWithKeys(fn($f) => [$f->id => $f->fields->map(fn($field) => ['id' => $field->id, 'name' => $field->name, 'crop' => $field->current_crop])]));

function loadFields(farmId) {
    const select = document.getElementById('add-field');
    select.innerHTML = '<option value="">— Unassigned —</option>';
    const fields = farmFields[farmId] || [];
    fields.forEach(f => {
        const opt = document.createElement('option');
        opt.value = f.id;
        opt.textContent = f.name + (f.crop ? ' · ' + f.crop : '');
        select.appendChild(opt);
    });
}

// ── Edit modal ────────────────────────────────────────────────────────────────
function openEditModal(id, name, type, fieldId, model) {
    document.getElementById('edit-form').action = '/sensors/' + id;
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-model').value = model;
    const typeSelect = document.getElementById('edit-type');
    for (let opt of typeSelect.options) {
        opt.selected = opt.value === type;
    }
    openModal('editModal');
}

// ── Real-time ping updates ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (window.Echo) {
        const farmIds = @json($farmIds ?? []);
        farmIds.forEach(id => {
            window.Echo.private('farm.' + id).listen('SensorDataUpdated', (e) => {
                const el = document.getElementById('sensor-ping-' + e.sensor_id);
                if (el) {
                    el.innerHTML = '<i class="fas fa-satellite-dish" style="color:#22c55e;width:14px"></i>'
                        + '<span style="color:var(--primary);font-weight:600">Pinged just now</span>';
                }
            });
        });
    }
});
</script>
@endsection
@endsection
