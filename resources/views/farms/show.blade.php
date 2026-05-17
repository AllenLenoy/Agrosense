@extends('layouts.app')
@section('title', $farm->name)
@section('breadcrumb', 'Farms / ' . $farm->name)

@section('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var farmLat = {{ $farm->latitude ?? 'null' }};
    var farmLng = {{ $farm->longitude ?? 'null' }};
    var defaultCenter = [31.6340, 74.8723]; // Default to Amritsar area if nothing found
    
    var mapCenter = (farmLat && farmLng) ? [farmLat, farmLng] : defaultCenter;
    var map = L.map('farm-map').setView(mapCenter, (farmLat && farmLng) ? 13 : 10);

    var osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    });

    var satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
    }).addTo(map);

    L.control.layers({
        "Satellite": satellite,
        "Street Map": osm
    }).addTo(map);

    if (farmLat && farmLng) {
        L.marker([farmLat, farmLng]).addTo(map).bindPopup('<b>{{ $farm->name }}</b><br>{{ $farm->location }}');
    } else {
        // Try to geocode the location string
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent('{{ $farm->location }}'))
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    var lat = data[0].lat;
                    var lon = data[0].lon;
                    map.setView([lat, lon], 15);
                    L.marker([lat, lon]).addTo(map).bindPopup('<b>{{ $farm->name }}</b><br>{{ $farm->location }}');
                }
            });
    }
    var drawnItems = new L.FeatureGroup();
    map.addLayer(drawnItems);
    var existingFields = @json($farm->fields->whereNotNull('geojson')->values());
    existingFields.forEach(function(field) {
        if (field.geojson) {
            var layer = L.geoJSON(field.geojson, {
                style: { color: field.status === 'active' ? '#22c55e' : (field.status === 'preparing' ? '#f59e0b' : '#3b82f6'), fillOpacity: 0.2 }
            }).bindPopup('<b>' + field.name + '</b><br>Crop: ' + (field.current_crop || 'None'));
            layer.eachLayer(function(l) { drawnItems.addLayer(l); });
        }
    });
    if (drawnItems.getLayers().length > 0) map.fitBounds(drawnItems.getBounds(), { padding: [20, 20] });
    var drawControl = new L.Control.Draw({
        edit: { featureGroup: drawnItems },
        draw: { polygon: { allowIntersection: false, showArea: true, shapeOptions: { color: '#22c55e', fillOpacity: 0.2 } }, polyline: false, circle: false, rectangle: false, marker: false, circlemarker: false }
    });
    map.addControl(drawControl);
    map.on(L.Draw.Event.CREATED, function (e) {
        drawnItems.addLayer(e.layer);
        toggleModal('addFieldModal');
        document.getElementById('geojson_input').value = JSON.stringify(e.layer.toGeoJSON());
    });
});
</script>
@endsection

@section('content')
<style>
.fs-back{display:flex;align-items:center;gap:.75rem;margin-bottom:.25rem}
.fs-back a{width:36px;height:36px;border-radius:50%;background:var(--bg-body);border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;color:var(--text-muted);text-decoration:none;transition:all .2s}
.fs-back a:hover{color:var(--primary);border-color:var(--primary)}
.fs-header{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap}
.fs-grid{display:grid;grid-template-columns:2fr 1fr;gap:1.5rem}
.fs-card{background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:1.5rem;margin-bottom:1.5rem}
.fs-card-title{font-size:.95rem;font-weight:700;color:var(--text-main);margin-bottom:.25rem}
.fs-card-desc{font-size:.8rem;color:var(--text-muted)}
.fs-map-wrap{border-radius:12px;overflow:hidden;border:1px solid var(--border-color);margin-bottom:1.5rem;position:relative}
.fs-map-badge{position:absolute;top:.75rem;right:.75rem;z-index:999;background:rgba(255,255,255,.9);backdrop-filter:blur(8px);padding:.35rem .75rem;border-radius:8px;border:1px solid var(--border-color);display:flex;align-items:center;gap:.5rem;font-size:.65rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px}
.fs-map-badge .dot{width:6px;height:6px;border-radius:50%;background:#22c55e;animation:pulse-soft 2s infinite}
@keyframes pulse-soft{0%,100%{opacity:1}50%{opacity:.3}}
.fs-field{background:var(--bg-body);border:1px solid var(--border-color);border-radius:10px;padding:1.25rem;transition:all .2s;cursor:pointer}
.fs-field:hover{border-color:var(--primary);transform:translateY(-2px)}
.fs-field-name{font-size:.9rem;font-weight:700;color:var(--text-main)}
.fs-field-status{font-size:.6rem;font-weight:700;padding:.15rem .5rem;border-radius:999px;text-transform:uppercase}
.fs-field-status.active{background:#dcfce7;color:#166534}.fs-field-status.preparing{background:#fef3c7;color:#92400e}.fs-field-status.fallow{background:#dbeafe;color:#1e40af}
.fs-field-meta{display:flex;gap:1rem;font-size:.8rem;color:var(--text-muted);margin:.5rem 0}
.fs-field-meta i{color:#94a3b8;width:14px}
.fs-crop-tag{display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;background:#fff;border:1px solid var(--border-color);padding:.2rem .5rem;border-radius:6px;color:var(--text-muted)}
.fs-sensor-item{display:flex;align-items:center;padding:.75rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:10px;text-decoration:none;transition:all .2s;margin-bottom:.5rem}
.fs-sensor-item:hover{border-color:var(--primary)}
.fs-sensor-icon{width:36px;height:36px;border-radius:8px;background:#fff;border:1px solid var(--border-color);display:flex;align-items:center;justify-content:center;margin-right:.75rem;position:relative}
.fs-sensor-dot{position:absolute;top:-3px;right:-3px;width:8px;height:8px;border-radius:50%;border:2px solid var(--bg-body)}
.fs-sensor-dot.on{background:#22c55e}.fs-sensor-dot.off{background:#ef4444}
.fs-alert-item{display:flex;gap:.75rem;align-items:flex-start;margin-bottom:.75rem}
.fs-alert-dot{width:8px;height:8px;border-radius:50%;margin-top:6px;flex-shrink:0}
.fs-alert-dot.critical{background:#ef4444;box-shadow:0 0 6px rgba(239,68,68,.5)}
.fs-alert-dot.warning{background:#f59e0b;box-shadow:0 0 6px rgba(245,158,11,.5)}
.fs-alert-dot.info{background:#3b82f6}
.fs-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem}
.fs-modal{background:#fff;border:1px solid var(--border-color);border-radius:16px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.1);overflow:hidden}
.fs-modal-header{padding:1.25rem 1.5rem;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center}
.fs-modal-header h3{font-size:1rem;font-weight:700;color:var(--text-main)}
.fs-modal-body{padding:1.5rem}
.fs-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem}
.fs-input{width:100%;padding:.65rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;margin-bottom:1rem}
.fs-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.fs-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.fs-btn-cancel{padding:.5rem 1rem;background:none;border:none;font-size:.85rem;font-weight:600;color:var(--text-muted);cursor:pointer}
.fs-btn-submit{padding:.5rem 1rem;background:var(--primary);color:white;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer}
.fs-btn-outline{padding:.5rem 1rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;color:var(--text-muted);display:inline-flex;align-items:center;gap:.5rem}
.hidden { display: none !important; }
@media(max-width:1024px){.fs-grid{grid-template-columns:1fr}}
</style>

<!-- Header -->
<div class="fs-back">
    <a href="{{ route('farms.index') }}"><i class="fas fa-arrow-left"></i></a>
    <div style="flex:1">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0">{{ $farm->name }}</h1>
                <p style="font-size:.8rem;color:var(--text-muted);margin-top:.15rem"><i class="fas fa-map-marker-alt"></i> {{ $farm->location }} · {{ $farm->area_acres }} acres · {{ $farm->soil_type }} soil</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <button onclick="toggleModal('editFarmModal')" class="fs-btn-outline"><i class="fas fa-edit"></i> Edit Farm</button>
                <form action="{{ route('farms.destroy', $farm) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this farm? This action cannot be undone.')" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="fs-btn-outline" style="color: #ef4444; border-color: #fca5a5;"><i class="fas fa-trash-alt"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="fs-grid" style="margin-top:1.5rem">
    <!-- Left Column -->
    <div>
        <!-- Map -->
        <div class="fs-map-wrap">
            <div id="farm-map" style="height:280px;width:100%;z-index:0"></div>
            <div class="fs-map-badge"><div class="dot"></div> Live Satellite</div>
        </div>

        <!-- Fields -->
        <div class="fs-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem">
                <div><div class="fs-card-title">Management Zones</div><div class="fs-card-desc">Configure individual field plots and assign crop tracking.</div></div>
                <button onclick="toggleModal('addFieldModal')" class="fs-btn-outline"><i class="fas fa-plus"></i> Add Zone</button>
            </div>
            @if($farm->fields->count() > 0)
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem">
                @foreach($farm->fields as $field)
                <div class="fs-field">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                        <span class="fs-field-name">{{ $field->name }}</span>
                        <span class="fs-field-status {{ $field->status }}">{{ ucfirst($field->status) }}</span>
                    </div>
                    <div class="fs-field-meta">
                        <span><i class="fas fa-ruler-combined"></i> {{ $field->area_acres }} ac</span>
                        <span><i class="fas fa-seedling"></i> {{ $field->current_crop ?? 'Idle' }}</span>
                    </div>
                    @if($field->crops->count() > 0)
                    <div style="padding-top:.5rem;border-top:1px solid var(--border-color);display:flex;flex-wrap:wrap;gap:.35rem;align-items:center;">
                        @foreach($field->crops as $crop)
                        <span class="fs-crop-tag" onclick="editCrop({{ $crop->id }}, '{{ addslashes($crop->name) }}', {{ $crop->health_score }})" style="cursor:pointer" title="Edit Crop">{{ $crop->name }} <span style="color:{{ $crop->health_score >= 80 ? '#22c55e' : '#f59e0b' }};font-weight:700">{{ $crop->health_score }}%</span></span>
                        @endforeach
                        <button onclick="addCrop({{ $field->id }})" style="background:none;border:1px dashed var(--border-color);border-radius:6px;padding:0.2rem 0.5rem;font-size:0.7rem;cursor:pointer;color:var(--text-muted)">+ Add</button>
                    </div>
                    @else
                    <div style="padding-top:.5rem;border-top:1px solid var(--border-color);display:flex;flex-wrap:wrap;gap:.35rem">
                        <button onclick="addCrop({{ $field->id }})" style="background:none;border:1px dashed var(--border-color);border-radius:6px;padding:0.2rem 0.5rem;font-size:0.7rem;cursor:pointer;color:var(--text-muted)">+ Add Crop</button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:2.5rem;border:2px dashed var(--border-color);border-radius:10px">
                <i class="fas fa-map" style="font-size:1.5rem;color:#cbd5e1;margin-bottom:.75rem"></i>
                <p style="font-size:.9rem;font-weight:600;color:var(--text-main);margin-bottom:.25rem">No zones defined</p>
                <p style="font-size:.8rem;color:var(--text-muted)">Draw field boundaries on the map.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Right Column -->
    <div>
        @if($farm->description)
        <div class="fs-card">
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.5rem">Profile Overview</div>
            <p style="font-size:.85rem;color:var(--text-muted);line-height:1.6">{{ $farm->description }}</p>
        </div>
        @endif

        <!-- Sensors -->
        <div class="fs-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <div><div class="fs-card-title">Connected Hardware</div><div class="fs-card-desc" style="margin-top:.15rem">IoT Nodes & Sensors</div></div>
                <button onclick="toggleModal('addSensorModal')" style="width:32px;height:32px;border-radius:8px;background:var(--bg-body);border:1px solid var(--border-color);cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted)"><i class="fas fa-plus" style="font-size:.7rem"></i></button>
            </div>
            @if($farm->sensors->count() > 0)
                @foreach($farm->sensors as $sensor)
                <a href="{{ route('sensors.show', $sensor) }}" class="fs-sensor-item">
                    <div class="fs-sensor-icon"><i class="fas fa-microchip" style="color:#64748b;font-size:.85rem"></i><div class="fs-sensor-dot {{ $sensor->status === 'online' ? 'on' : 'off' }}"></div></div>
                    <div style="flex:1;min-width:0"><p style="font-size:.85rem;font-weight:600;color:var(--text-main);overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $sensor->name }}</p><p style="font-size:.7rem;color:var(--text-muted);font-family:monospace;margin-top:.1rem">{{ $sensor->device_id }}</p></div>
                    <i class="fas fa-chevron-right" style="font-size:.6rem;color:#cbd5e1"></i>
                </a>
                @endforeach
            @else
            <div style="text-align:center;padding:2rem"><i class="fas fa-wifi" style="font-size:1.5rem;color:#cbd5e1;margin-bottom:.5rem"></i><p style="font-size:.85rem;font-weight:600;color:var(--text-main)">No hardware paired</p><p style="font-size:.8rem;color:var(--text-muted)">Connect sensors to stream live data.</p></div>
            @endif
        </div>

        <!-- Alerts -->
        <div class="fs-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <div class="fs-card-title">Event Log</div>
                <a href="{{ route('alerts.index') }}" style="font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--text-muted);text-decoration:none">View All</a>
            </div>
            @if($farm->alerts->count() > 0)
                @foreach($farm->alerts->take(4) as $alert)
                <div class="fs-alert-item">
                    <div class="fs-alert-dot {{ $alert->type === 'critical' ? 'critical' : ($alert->type === 'warning' ? 'warning' : 'info') }}"></div>
                    <div><p style="font-size:.85rem;color:var(--text-main);line-height:1.4">{{ $alert->title }}</p><p style="font-size:.7rem;color:var(--text-muted);font-family:monospace;margin-top:.25rem">{{ $alert->created_at->diffForHumans() }}</p></div>
                </div>
                @endforeach
            @else
            <div style="text-align:center;padding:1.5rem;border-top:1px solid var(--border-color)"><i class="fas fa-check" style="color:#cbd5e1;margin-bottom:.5rem"></i><p style="font-size:.85rem;color:var(--text-muted)">All telemetry nominal.</p></div>
            @endif
        </div>
    </div>
</div>

<!-- Add Field Modal -->
<div id="addFieldModal" class="fs-modal-bg {{ $errors->has('area_acres') || $errors->has('current_crop') || $errors->has('geojson') ? '' : 'hidden' }}">
    <div class="fs-modal">
        <div class="fs-modal-header"><h3>Configure Zone</h3><button onclick="toggleModal('addFieldModal')" style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i class="fas fa-times"></i></button></div>
        <div class="fs-modal-body">
            @if ($errors->has('area_acres') || $errors->has('current_crop') || $errors->has('geojson') || $errors->has('name'))
                <div style="background-color: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('fields.store', $farm) }}" method="POST">
                @csrf
                <input type="hidden" name="geojson" id="geojson_input" value="{{ old('geojson') }}">
                <label class="fs-label">Zone Identifier</label><input type="text" name="name" required placeholder="e.g. North Plot A" class="fs-input" value="{{ old('name') }}">
                <div class="fs-row">
                    <div>
                        <label class="fs-label">Size (Acres)</label>
                        <input type="number" step="0.01" name="area_acres" required placeholder="0.00" class="fs-input" style="font-family:monospace" value="{{ old('area_acres') }}">
                    </div>
                    <div>
                        <label class="fs-label">Substrate / Soil Type</label>
                        <select name="soil_type" class="fs-input">
                            @foreach(['Loam','Clay','Sandy','Silt','Sandy Loam','Clay Loam','Silty Clay','Peat','Chalky','Peaty'] as $soil)
                            <option value="{{ $soil }}" {{ old('soil_type','Loam') == $soil ? 'selected' : '' }}>{{ $soil }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="fs-row">
                    <div>
                        <label class="fs-label">Active Crop</label>
                        <select name="current_crop" class="fs-input">
                            <option value="">— None / Fallow —</option>
                            @php
                                $crops = ['Wheat','Rice','Maize (Corn)','Sugarcane','Cotton','Soybean','Barley','Sorghum','Millet','Oats','Sunflower','Canola (Rapeseed)','Groundnut (Peanut)','Chickpea','Lentil','Potato','Tomato','Onion','Garlic','Chilli Pepper','Cabbage','Cauliflower','Spinach','Carrot','Cucumber','Pumpkin','Watermelon','Mango','Banana','Papaya','Grapes','Strawberry','Apple','Orange','Lemon','Coffee','Tea','Turmeric','Ginger','Coriander'];
                            @endphp
                            @foreach($crops as $crop)
                            <option value="{{ $crop }}" {{ old('current_crop') == $crop ? 'selected' : '' }}>{{ $crop }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="fs-label">Zone Status</label>
                        <select name="status" class="fs-input">
                            <option value="active"     {{ old('status','active') == 'active'     ? 'selected' : '' }}>🟢 Active</option>
                            <option value="preparing"  {{ old('status') == 'preparing'  ? 'selected' : '' }}>🟡 Preparing</option>
                            <option value="fallow"     {{ old('status') == 'fallow'     ? 'selected' : '' }}>⚪ Fallow</option>
                        </select>
                    </div>
                </div>
                <div style="display:flex;justify-content:flex-end;gap:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color)"><button type="button" onclick="toggleModal('addFieldModal')" class="fs-btn-cancel">Cancel</button><button type="submit" class="fs-btn-submit">Initialize Zone</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Add Sensor Modal -->
<div id="addSensorModal" class="fs-modal-bg {{ $errors->has('device_id') || $errors->has('type') || $errors->has('field_id') ? '' : 'hidden' }}">
    <div class="fs-modal">
        <div class="fs-modal-header"><h3>Pair Hardware</h3><button onclick="toggleModal('addSensorModal')" style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i class="fas fa-times"></i></button></div>
        <div class="fs-modal-body">
            @if ($errors->has('device_id') || $errors->has('type') || $errors->has('field_id') || $errors->has('name'))
                <div style="background-color: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('sensors.store', $farm) }}" method="POST">
                @csrf
                <label class="fs-label">Device ID / MAC</label>
                <input type="text" name="device_id" required placeholder="e.g. ESP32-A8F9" class="fs-input" style="font-family:monospace" value="{{ old('device_id') }}">
                <label class="fs-label">Hardware Type</label>
                <select name="type" id="sensor-type-select" required class="fs-input" onchange="autoFillSensorName(this)">
                    @php
                        $sensorTypes = [
                            'soil_moisture'   => 'Soil Moisture Node',
                            'temperature'     => 'Temperature Sensor',
                            'humidity'        => 'Humidity Sensor',
                            'weather_station' => 'Weather Station (Multi-sensor)',
                            'water_level'     => 'Water Level Sensor',
                            'ph_sensor'       => 'Soil pH Sensor',
                            'light'           => 'Light Intensity Sensor',
                            'wind'            => 'Wind Speed / Direction Sensor',
                            'rainfall'        => 'Rain Gauge',
                            'camera'          => 'Optical / Camera Node',
                            'irrigation_valve'=> 'Irrigation Valve Controller',
                        ];
                    @endphp
                    @foreach($sensorTypes as $val => $label)
                    <option value="{{ $val }}" {{ old('type','soil_moisture') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <label class="fs-label">Display Name</label>
                <input type="text" name="name" id="sensor-name-input" required placeholder="e.g. Main Soil Probe" class="fs-input" value="{{ old('name') }}">
                <label class="fs-label">Zone Assignment (Optional)</label>
                <select name="field_id" class="fs-input">
                    <option value="">— Unassigned —</option>
                    @foreach($farm->fields as $field)
                    <option value="{{ $field->id }}" {{ old('field_id') == $field->id ? 'selected' : '' }}>
                        {{ $field->name }}{{ $field->current_crop ? ' · ' . $field->current_crop : '' }}
                    </option>
                    @endforeach
                </select>
                <div style="display:flex;justify-content:flex-end;gap:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color)"><button type="button" onclick="toggleModal('addSensorModal')" class="fs-btn-cancel">Cancel</button><button type="submit" class="fs-btn-submit">Complete Pairing</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Farm Modal -->
<div id="editFarmModal" class="fs-modal-bg hidden">
    <div class="fs-modal">
        <div class="fs-modal-header"><h3>Edit Farm Details</h3><button onclick="toggleModal('editFarmModal')" style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i class="fas fa-times"></i></button></div>
        <div class="fs-modal-body">
            <form action="{{ route('farms.update', $farm) }}" method="POST">
                @csrf
                @method('PUT')
                <label class="fs-label">Farm Name</label>
                <input name="name" required class="fs-input" value="{{ old('name', $farm->name) }}">

                <label class="fs-label">Location</label>
                <div style="position:relative;margin-bottom:.25rem">
                    <input name="location" id="edit-location-input" required class="fs-input"
                        value="{{ old('location', $farm->location) }}"
                        style="margin-bottom:0;padding-right:2.5rem"
                        oninput="clearEditCoords()">
                    <button type="button" onclick="useGPS('edit')" title="Use my current location"
                        style="position:absolute;right:.6rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--primary);font-size:.95rem">
                        <i class="fas fa-location-arrow"></i>
                    </button>
                </div>
                <div id="edit-geocode-status" style="font-size:.75rem;margin-bottom:.75rem;display:none"></div>

                {{-- Show current coordinates if set --}}
                @if($farm->latitude && $farm->longitude)
                <div style="font-size:.72rem;color:#166534;background:#dcfce7;padding:.4rem .65rem;border-radius:6px;margin-bottom:.75rem">
                    <i class="fas fa-map-pin"></i>
                    Current: {{ $farm->latitude }}, {{ $farm->longitude }}
                    — weather is active
                </div>
                @endif

                <input type="hidden" name="latitude"  id="edit-lat"  value="{{ old('latitude',  $farm->latitude) }}">
                <input type="hidden" name="longitude" id="edit-lng"  value="{{ old('longitude', $farm->longitude) }}">

                <div class="fs-row">
                    <div>
                        <label class="fs-label">Area (acres)</label>
                        <input name="area_acres" type="number" step="0.01" class="fs-input" value="{{ old('area_acres', $farm->area_acres) }}">
                    </div>
                    <div>
                        <label class="fs-label">Soil Type</label>
                        <select name="soil_type" class="fs-input">
                            @foreach(['Loam','Clay','Sandy','Silt','Sandy Loam','Clay Loam','Silty Clay','Peat','Chalky','Alluvial'] as $s)
                            <option value="{{ $s }}" {{ old('soil_type', $farm->soil_type) == $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <label class="fs-label">Description</label>
                <textarea name="description" rows="3" class="fs-input" style="resize:vertical">{{ old('description', $farm->description) }}</textarea>
                <div style="display:flex;justify-content:flex-end;gap:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color)">
                    <button type="button" onclick="toggleModal('editFarmModal')" class="fs-btn-cancel">Cancel</button>
                    <button type="submit" class="fs-btn-submit">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Crop Modal -->
<div id="cropModal" class="fs-modal-bg hidden">
    <div class="fs-modal">
        <div class="fs-modal-header"><h3 id="cropModalTitle">Add Crop</h3><button type="button" onclick="toggleModal('cropModal')" style="background:none;border:none;color:var(--text-muted);cursor:pointer"><i class="fas fa-times"></i></button></div>
        <div class="fs-modal-body">
            <form id="cropForm" method="POST" action="">
                @csrf
                <input type="hidden" name="_method" id="cropMethod" value="POST">
                <label class="fs-label">Crop Name</label>
                <input type="text" name="name" id="cropName" required class="fs-input" placeholder="e.g. Wheat">
                <label class="fs-label">Health Score (0-100)</label>
                <input type="number" name="health_score" id="cropHealth" required class="fs-input" min="0" max="100" value="100">
                <div style="display:flex;justify-content:space-between;gap:.75rem;padding-top:.75rem;border-top:1px solid var(--border-color)">
                    <button type="button" id="deleteCropBtn" onclick="deleteCrop()" class="fs-btn-cancel hidden" style="color:#ef4444;padding-left:0"><i class="fas fa-trash"></i> Delete</button>
                    <div style="display:flex;gap:.75rem;margin-left:auto">
                        <button type="button" onclick="toggleModal('cropModal')" class="fs-btn-cancel">Cancel</button>
                        <button type="submit" class="fs-btn-submit">Save Crop</button>
                    </div>
                </div>
            </form>
            <form id="deleteCropForm" method="POST" action="" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</div>

<script>
function toggleModal(id) {
    document.getElementById(id).classList.toggle('hidden');
}

function addCrop(fieldId) {
    document.getElementById('cropModalTitle').innerText = 'Add Crop';
    document.getElementById('cropForm').action = `/fields/${fieldId}/crops`;
    document.getElementById('cropMethod').value = 'POST';
    document.getElementById('cropName').value = '';
    document.getElementById('cropHealth').value = '100';
    document.getElementById('deleteCropBtn').classList.add('hidden');
    toggleModal('cropModal');
}

function editCrop(cropId, name, health) {
    document.getElementById('cropModalTitle').innerText = 'Edit Crop';
    document.getElementById('cropForm').action = `/crops/${cropId}`;
    document.getElementById('cropMethod').value = 'PUT';
    document.getElementById('cropName').value = name;
    document.getElementById('cropHealth').value = health;
    document.getElementById('deleteCropForm').action = `/crops/${cropId}`;
    document.getElementById('deleteCropBtn').classList.remove('hidden');
    toggleModal('cropModal');
}

function deleteCrop() {
    if (confirm('Are you sure you want to delete this crop?')) {
        document.getElementById('deleteCropForm').submit();
    }
}

// ── Geocoding helpers (Edit Farm modal) ───────────────────────────────────────
function clearEditCoords() {
    document.getElementById('edit-lat').value = '';
    document.getElementById('edit-lng').value = '';
    showEditStatus('', '');
}

function showEditStatus(msg, color) {
    const el = document.getElementById('edit-geocode-status');
    if (!el) return;
    el.textContent = msg;
    el.style.color = color;
    el.style.display = msg ? 'block' : 'none';
}

function useGPS(prefix) {
    if (!navigator.geolocation) {
        showEditStatus('⚠️ GPS not supported by your browser.', '#b91c1c');
        return;
    }
    showEditStatus('⏳ Getting your location…', '#64748b');
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude.toFixed(7);
            const lng = pos.coords.longitude.toFixed(7);
            document.getElementById('edit-lat').value = lat;
            document.getElementById('edit-lng').value = lng;
            showEditStatus('✅ GPS set: ' + lat + ', ' + lng, '#166534');
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(r => r.json())
                .then(data => {
                    const addr = data.address;
                    const label = [addr.village || addr.town || addr.city, addr.state, addr.country]
                        .filter(Boolean).join(', ');
                    if (label) document.getElementById('edit-location-input').value = label;
                })
                .catch(() => {});
        },
        () => showEditStatus('⚠️ GPS access denied. Enter location manually.', '#b91c1c')
    );
}

document.addEventListener('DOMContentLoaded', () => {
    const editInput = document.getElementById('edit-location-input');
    if (editInput) {
        editInput.addEventListener('blur', () => {
            if (!document.getElementById('edit-lat').value) {
                const query = editInput.value.trim();
                if (query.length < 4) return;
                showEditStatus('⏳ Looking up coordinates…', '#64748b');
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query))
                    .then(r => r.json())
                    .then(data => {
                        if (data.length > 0) {
                            const lat = parseFloat(data[0].lat).toFixed(7);
                            const lng = parseFloat(data[0].lon).toFixed(7);
                            document.getElementById('edit-lat').value = lat;
                            document.getElementById('edit-lng').value = lng;
                            showEditStatus('✅ Coordinates found: ' + lat + ', ' + lng + ' — weather will be fetched automatically.', '#166534');
                        } else {
                            showEditStatus('⚠️ Location not found. Try a more specific name.', '#92400e');
                        }
                    })
                    .catch(() => showEditStatus('⚠️ Geocoding failed.', '#b91c1c'));
            }
        });
    }
});

// ── Sensor name auto-fill ─────────────────────────────────────────────────────
const sensorNameSuggestions = {
    'soil_moisture':    'Soil Moisture Probe',
    'temperature':      'Temperature Sensor',
    'humidity':         'Humidity Sensor',
    'weather_station':  'Weather Station',
    'water_level':      'Water Level Sensor',
    'ph_sensor':        'Soil pH Sensor',
    'light':            'Light Intensity Sensor',
    'wind':             'Wind Sensor',
    'rainfall':         'Rain Gauge',
    'camera':           'Optical Node',
    'irrigation_valve': 'Irrigation Valve',
};

function autoFillSensorName(select) {
    const nameInput = document.getElementById('sensor-name-input');
    const suggestion = sensorNameSuggestions[select.value] || '';
    const currentVal = nameInput.value.trim();
    const wasAutoFilled = Object.values(sensorNameSuggestions).includes(currentVal) || currentVal === '';
    if (wasAutoFilled) nameInput.value = suggestion;
}
</script>
@endsection
