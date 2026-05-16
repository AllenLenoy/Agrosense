@extends('layouts.app')
@section('title', 'Disease AI')
@section('breadcrumb', 'Intelligence / Disease AI')

@section('content')
<style>
    .dis-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .dis-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .dis-stat { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem; }
    .dis-stat-label { font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
    .dis-stat-value { font-size: 1.75rem; font-weight: 800; }
    .dis-card { background: #fff; border: 1px solid var(--border-color); border-radius: 12px; padding: 1.5rem; margin-bottom: 1rem; display: flex; gap: 1.5rem; transition: all 0.2s; }
    .dis-card:hover { box-shadow: 0 8px 20px rgba(0,0,0,0.04); }
    .dis-image { width: 200px; height: 150px; background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 10px; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .dis-image i { font-size: 2rem; color: #cbd5e1; }
    .dis-body { flex: 1; }
    .dis-title-row { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem; }
    .dis-title { font-size: 1.05rem; font-weight: 700; color: var(--text-main); }
    .sev-badge { font-size: 0.6rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .sev-critical { background: #fee2e2; color: #dc2626; }
    .sev-high { background: #fef3c7; color: #d97706; }
    .sev-medium { background: #dbeafe; color: #2563eb; }
    .sev-low { background: #dcfce7; color: #166534; }
    .status-badge { font-size: 0.6rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.5px; }
    .st-analyzed { background: #dbeafe; color: #2563eb; }
    .st-treated { background: #dcfce7; color: #166534; }
    .st-pending { background: #fef3c7; color: #d97706; }
    .conf-bar { width: 120px; height: 4px; background: #e2e8f0; border-radius: 99px; overflow: hidden; }
    .conf-fill { height: 100%; background: var(--primary); border-radius: 99px; }
    .dis-info-box { background: var(--bg-body); border: 1px solid var(--border-color); border-radius: 10px; padding: 1rem; margin-bottom: 0.75rem; }
    .dis-info-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.35rem; }
    .dis-info-title.treatment { color: var(--primary); }
    .dis-info-title.prevention { color: #3b82f6; }
    .dis-info-text { font-size: 0.85rem; color: var(--text-main); line-height: 1.6; }
    .dis-meta { display: flex; align-items: center; gap: 1rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--border-color); font-size: 0.75rem; color: var(--text-muted); }
    .dis-meta i { width: 14px; color: #94a3b8; }
    .btn-upload { padding: 0.65rem 1.25rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; transition: all 0.2s; }
    .btn-upload:hover { background: var(--primary-dark); transform: translateY(-1px); }
    .empty-dis { text-align: center; padding: 4rem; background: #fff; border: 2px dashed var(--border-color); border-radius: 12px; }
    @media (max-width: 768px) { .dis-stats { grid-template-columns: repeat(2, 1fr); } .dis-card { flex-direction: column; } .dis-image { width: 100%; height: 120px; } }
</style>

<div class="dis-header">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin: 0;">Disease Intelligence</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">AI-powered crop pathology analysis and treatment planning.</p>
    </div>
    <a href="{{ route('diseases.create') }}" class="btn-upload"><i class="fas fa-upload"></i> Upload Image</a>
</div>

<div class="dis-stats">
    <div class="dis-stat">
        <div class="dis-stat-label">Total Scans</div>
        <div class="dis-stat-value" style="color: var(--text-main);">{{ $stats['total'] }}</div>
    </div>
    <div class="dis-stat">
        <div class="dis-stat-label">Pending</div>
        <div class="dis-stat-value" style="color: #f59e0b;">{{ $stats['pending'] }}</div>
    </div>
    <div class="dis-stat">
        <div class="dis-stat-label">Treated</div>
        <div class="dis-stat-value" style="color: #22c55e;">{{ $stats['treated'] }}</div>
    </div>
    <div class="dis-stat">
        <div class="dis-stat-label">Critical</div>
        <div class="dis-stat-value" style="color: #ef4444;">{{ $stats['critical'] }}</div>
    </div>
</div>

@forelse($reports as $report)
<div class="dis-card">
    <div class="dis-image">
        <i class="fas fa-camera"></i>
    </div>
    <div class="dis-body">
        <div class="dis-title-row">
            <span class="dis-title">{{ $report->disease_name ?? 'Analysis in progress...' }}</span>
            <span class="sev-badge sev-{{ $report->severity }}">{{ ucfirst($report->severity) }}</span>
            <span class="status-badge st-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
        </div>

        @if($report->confidence)
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem;">
            <span style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">Confidence</span>
            <div class="conf-bar"><div class="conf-fill" style="width:{{ $report->confidence }}%"></div></div>
            <span style="font-size: 0.8rem; font-family: monospace; font-weight: 600; color: var(--primary);">{{ $report->confidence }}%</span>
        </div>
        @endif

        @if($report->description)
        <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 0.75rem;">{{ $report->description }}</p>
        @endif

        @if($report->treatment)
        <div class="dis-info-box">
            <p class="dis-info-title treatment"><i class="fas fa-prescription-bottle-medical"></i> Recommended Treatment</p>
            <p class="dis-info-text">{{ $report->treatment }}</p>
        </div>
        @endif

        @if($report->prevention)
        <div class="dis-info-box">
            <p class="dis-info-title prevention"><i class="fas fa-shield-alt"></i> Prevention Plan</p>
            <p class="dis-info-text">{{ $report->prevention }}</p>
        </div>
        @endif

        <div class="dis-meta">
            <span><i class="fas fa-map-marker-alt"></i> {{ $report->farm->name }}</span>
            @if($report->crop)<span><i class="fas fa-seedling"></i> {{ $report->crop->name }}</span>@endif
            <span><i class="far fa-clock"></i> {{ $report->created_at->diffForHumans() }}</span>
        </div>
    </div>
</div>
@empty
<div class="empty-dis">
    <i class="fas fa-microscope" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No disease reports</h3>
    <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.5rem;">Upload a crop image to get AI-powered disease analysis.</p>
    <a href="{{ route('diseases.create') }}" class="btn-upload">Upload Image</a>
</div>
@endforelse

<div style="margin-top: 1.5rem;">{{ $reports->links() }}</div>
@endsection
