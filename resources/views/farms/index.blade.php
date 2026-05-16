@extends('layouts.app')
@section('title', 'Farms')
@section('breadcrumb', 'Operations / Farms')

@section('content')
<style>
.fm-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem}
.fm-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem}
.fm-card{background:#fff;border:1px solid var(--border-color);border-radius:12px;overflow:hidden;transition:all .25s;text-decoration:none;display:block}
.fm-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.06);border-color:var(--primary)}
.fm-banner{height:100px;background:linear-gradient(135deg,#e8f5e9,#c8e6c9);display:flex;align-items:center;justify-content:center;border-bottom:1px solid var(--border-color)}
.fm-banner i{font-size:2rem;color:var(--primary);opacity:.4}
.fm-body{padding:1.25rem}
.fm-name{font-size:.95rem;font-weight:700;color:var(--text-main)}
.fm-active-badge{font-size:.6rem;font-weight:700;padding:.15rem .5rem;border-radius:999px;text-transform:uppercase}
.fm-active{background:#dcfce7;color:#166534}.fm-inactive{background:#f1f5f9;color:#64748b}
.fm-loc{font-size:.8rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;margin:.35rem 0 1rem}
.fm-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;text-align:center}
.fm-stat-box{background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;padding:.5rem}
.fm-stat-val{font-size:.9rem;font-weight:700;color:var(--text-main)}
.fm-stat-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.3px;color:var(--text-muted);margin-top:.15rem}
.fm-soil{font-size:.75rem;color:var(--text-muted);display:flex;align-items:center;gap:.35rem;margin-top:.75rem}
.fm-empty{grid-column:span 3;text-align:center;padding:4rem;background:#fff;border:2px dashed var(--border-color);border-radius:12px}
.fm-btn{padding:.65rem 1.25rem;background:var(--primary);color:white;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:.5rem}
.fm-btn:hover{background:var(--primary-dark);transform:translateY(-1px)}
.fm-modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.5);backdrop-filter:blur(4px);z-index:50;display:flex;align-items:center;justify-content:center;padding:1rem}
.fm-modal{background:#fff;border:1px solid var(--border-color);border-radius:16px;padding:1.5rem;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.1)}
.fm-modal h3{font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:1.25rem}
.fm-label{display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:.35rem}
.fm-input{width:100%;padding:.65rem .75rem;border:1px solid var(--border-color);border-radius:8px;font-size:.9rem;color:var(--text-main);background:var(--bg-body);outline:none;margin-bottom:1rem}
.fm-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(33,150,83,.1)}
.fm-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.fm-actions{display:flex;gap:.75rem;padding-top:.75rem}
.fm-btn-cancel{flex:1;padding:.65rem;background:var(--bg-body);border:1px solid var(--border-color);border-radius:8px;font-weight:600;font-size:.85rem;cursor:pointer;color:var(--text-muted)}
.fm-btn-submit{flex:1;padding:.65rem;background:var(--primary);color:white;border:none;border-radius:8px;font-weight:700;font-size:.85rem;cursor:pointer}
.hidden { display: none !important; }
.fm-modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.fm-modal-close { background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.25rem; transition: color 0.2s; }
.fm-modal-close:hover { color: #ef4444; }
@media(max-width:1024px){.fm-grid{grid-template-columns:repeat(2,1fr)}.fm-empty{grid-column:span 2}}
@media(max-width:640px){.fm-grid{grid-template-columns:1fr}.fm-empty{grid-column:span 1}}
</style>

<div class="fm-header">
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;color:var(--text-main);margin:0">Farm Management</h1>
        <p style="color:var(--text-muted);font-size:.9rem;margin-top:.25rem">{{ $farms->total() }} farms registered</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ url()->previous() == url()->current() ? route('dashboard') : url()->previous() }}" class="fm-btn" style="background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0;">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <button onclick="document.getElementById('add-farm-modal').classList.remove('hidden')" class="fm-btn">
            <i class="fas fa-plus"></i> Add Farm
        </button>
    </div>
</div>

<div class="fm-grid">
    @forelse($farms as $farm)
    <a href="{{ route('farms.show', $farm) }}" class="fm-card">
        <div class="fm-banner"><i class="fas fa-tractor"></i></div>
        <div class="fm-body">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.25rem">
                <span class="fm-name">{{ $farm->name }}</span>
                <span class="fm-active-badge {{ $farm->is_active ? 'fm-active' : 'fm-inactive' }}">{{ $farm->is_active ? 'Active' : 'Inactive' }}</span>
            </div>
            <div class="fm-loc"><i class="fas fa-map-marker-alt"></i> {{ $farm->location }}</div>
            <div class="fm-stats">
                <div class="fm-stat-box"><div class="fm-stat-val">{{ $farm->fields->count() }}</div><div class="fm-stat-label">Fields</div></div>
                <div class="fm-stat-box"><div class="fm-stat-val">{{ $farm->sensors->count() }}</div><div class="fm-stat-label">Sensors</div></div>
                <div class="fm-stat-box"><div class="fm-stat-val">{{ $farm->area_acres ?: '—' }}</div><div class="fm-stat-label">Acres</div></div>
            </div>
            @if($farm->soil_type)<div class="fm-soil"><i class="fas fa-layer-group"></i> {{ $farm->soil_type }} soil</div>@endif
        </div>
    </a>
    @empty
    <div class="fm-empty">
        <i class="fas fa-tractor" style="font-size:2.5rem;color:#cbd5e1;margin-bottom:1rem"></i>
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:.5rem">No farms yet</h3>
        <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:1.5rem">Create your first farm to get started.</p>
        <button onclick="document.getElementById('add-farm-modal').classList.remove('hidden')" class="fm-btn">Add Farm</button>
    </div>
    @endforelse
</div>
<div style="margin-top:1.5rem">{{ $farms->links() }}</div>

<!-- Modal -->
<div id="add-farm-modal" class="fm-modal-bg {{ $errors->any() ? '' : 'hidden' }}">
    <div class="fm-modal">
        <div class="fm-modal-header">
            <h3 style="margin: 0;">Add New Farm</h3>
            <button type="button" onclick="document.getElementById('add-farm-modal').classList.add('hidden')" class="fm-modal-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        @if ($errors->any())
            <div style="background-color: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <ul style="list-style-position: inside; margin: 0; padding: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('farms.store') }}">
            @csrf
            <label class="fm-label">Farm Name</label>
            <input name="name" required class="fm-input" placeholder="e.g. Punjab Wheat Farm" value="{{ old('name') }}">
            <label class="fm-label">Location</label>
            <input name="location" required class="fm-input" placeholder="e.g. Ludhiana, Punjab" value="{{ old('location') }}">
            <div class="fm-row">
                <div><label class="fm-label">Area (acres)</label><input name="area_acres" type="number" step="0.01" class="fm-input" placeholder="85.5" value="{{ old('area_acres') }}"></div>
                <div><label class="fm-label">Soil Type</label><input name="soil_type" class="fm-input" placeholder="Alluvial" value="{{ old('soil_type') }}"></div>
            </div>
            <label class="fm-label">Description</label>
            <textarea name="description" rows="2" class="fm-input" placeholder="Brief description..." style="resize:vertical">{{ old('description') }}</textarea>
            <div class="fm-actions">
                <button type="button" onclick="document.getElementById('add-farm-modal').classList.add('hidden')" class="fm-btn-cancel">Cancel</button>
                <button type="submit" class="fm-btn-submit">Create Farm</button>
            </div>
        </form>
    </div>
</div>
@endsection
