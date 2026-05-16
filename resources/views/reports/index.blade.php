@extends('layouts.app')
@section('title', 'Compliance & Reports')
@section('breadcrumb', 'Reports')

@section('content')
<style>
    .rpt-header { margin-bottom: 1.5rem; }
    .rpt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .rpt-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 2rem; }
    .rpt-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .rpt-icon.pdf { background: #fee2e2; }
    .rpt-icon.pdf i { color: #ef4444; font-size: 1.25rem; }
    .rpt-icon.csv { background: #dcfce7; }
    .rpt-icon.csv i { color: #22c55e; font-size: 1.25rem; }
    .rpt-title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    .rpt-desc { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.35rem; line-height: 1.6; }
    .rpt-form { padding-top: 1.5rem; margin-top: 1.5rem; border-top: 1px solid var(--border-color); }
    .rpt-label { display: block; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); margin-bottom: 0.35rem; }
    .rpt-select, .rpt-input { width: 100%; padding: 0.65rem 0.75rem; border: 1px solid var(--border-color); border-radius: 8px; font-size: 0.9rem; color: var(--text-main); background: var(--bg-body); outline: none; }
    .rpt-select:focus, .rpt-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(33,150,83,0.1); }
    .rpt-btn-pdf { width: 100%; padding: 0.75rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .rpt-btn-pdf:hover { background: var(--primary-dark); }
    .rpt-btn-csv { width: 100%; padding: 0.75rem; background: var(--bg-body); color: var(--primary); border: 1px solid var(--primary); border-radius: 8px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
    .rpt-btn-csv:hover { background: rgba(33,150,83,0.05); }
    .rpt-date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
    @media (max-width: 768px) { .rpt-grid { grid-template-columns: 1fr; } }
</style>

<div class="rpt-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Compliance & Reports</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">Generate and export data for audits, analysis, and record-keeping.</p>
    </div>
    <a href="{{ url()->previous() == url()->current() ? route('dashboard') : url()->previous() }}" class="rpt-btn-csv" style="width: auto; padding: 0.5rem 1rem;">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

@if ($errors->any())
    <div style="background-color: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.85rem; border: 1px solid #fecaca;">
        <ul style="margin: 0; padding-left: 1.2rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($farms->isEmpty())
    <div style="grid-column: span 2; text-align: center; padding: 4rem; background: #fff; border: 2px dashed var(--border-color); border-radius: 12px;">
        <i class="fas fa-file-alt" style="font-size:2.5rem;color:#cbd5e1;margin-bottom:1rem"></i>
        <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-main);margin-bottom:.5rem">No Data Available</h3>
        <p style="font-size:.9rem;color:var(--text-muted);margin-bottom:1.5rem">You need to register a farm before you can generate reports or export data.</p>
        <a href="{{ route('farms.index') }}" class="btn btn-primary">Go to Farm Management</a>
    </div>
@else
<div class="rpt-grid">
    <!-- PDF Report -->
    <div class="rpt-card">
        <div class="rpt-card-header">
            <h3 class="rpt-card-title"><i class="far fa-file-pdf" style="color: #ef4444; margin-right: 0.5rem;"></i> Official Compliance Report</h3>
            <span class="rpt-badge">PDF</span>
        </div>
        <div class="rpt-card-body">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">Generates a comprehensive PDF document detailing farm status, active IoT devices, recent critical alerts, and AI disease detections. Suitable for audits and official record-keeping.</p>
            
            <form action="{{ route('reports.export-pdf') }}" method="POST" class="rpt-form">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label class="rpt-label">Select Farm</label>
                    <select name="farm_id" required class="rpt-select">
                        @foreach($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rpt-btn-pdf"><i class="fas fa-download"></i> Generate PDF</button>
            </form>
        </div>
    </div>

    <!-- CSV Export -->
    <div class="rpt-card">
        <div class="rpt-card-header">
            <h3 class="rpt-card-title"><i class="far fa-file-excel" style="color: #10b981; margin-right: 0.5rem;"></i> Raw Telemetry Export</h3>
            <span class="rpt-badge" style="background: #dcfce7; color: #166534;">CSV</span>
        </div>
        <div class="rpt-card-body">
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">Export raw sensor telemetry data for a specific date range. Includes soil moisture, temperature, humidity, and chemical readings for external data analysis tools.</p>
            
            <form action="{{ route('reports.export-csv') }}" method="POST" class="rpt-form">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label class="rpt-label">Select Farm</label>
                    <select name="farm_id" required class="rpt-select">
                        @foreach($farms as $farm)
                        <option value="{{ $farm->id }}">{{ $farm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <label class="rpt-label">Start Date</label>
                        <input type="date" name="start_date" required class="rpt-select">
                    </div>
                    <div style="flex: 1;">
                        <label class="rpt-label">End Date</label>
                        <input type="date" name="end_date" required class="rpt-select">
                    </div>
                </div>
                <button type="submit" class="rpt-btn-csv"><i class="fas fa-file-export"></i> Download CSV Data</button>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
