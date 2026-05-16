@extends('layouts.app')
@section('title', 'Search Results')
@section('breadcrumb', 'Search')

@section('content')
<div style="background: #fff; border-radius: 16px; border: 1px solid var(--border-color); padding: 2rem;">
    <h2 style="font-size: 1.25rem; font-weight: 800; color: var(--text-main); margin-bottom: 0.5rem;">Search Results for "{{ $query }}"</h2>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem;">Found {{ $farms->count() }} farms, {{ $sensors->count() }} sensors, and {{ $alerts->count() }} alerts.</p>

    @if($farms->isEmpty() && $sensors->isEmpty() && $alerts->isEmpty())
        <div style="text-align: center; padding: 3rem 0; color: var(--text-muted);">
            <i class="fas fa-search" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem;"></i>
            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">No results found</h3>
            <p>We couldn't find anything matching your search. Try adjusting your keywords.</p>
        </div>
    @endif

    @if($farms->isNotEmpty())
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;"><i class="fas fa-map-marked-alt text-green-600 mr-2"></i> Farms</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
            @foreach($farms as $farm)
            <a href="{{ route('farms.show', $farm) }}" style="display: block; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; text-decoration: none; transition: border-color 0.2s;">
                <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem;">{{ $farm->name }}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fas fa-map-marker-alt"></i> {{ $farm->location }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($sensors->isNotEmpty())
    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;"><i class="fas fa-microchip text-blue-600 mr-2"></i> Sensors</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
            @foreach($sensors as $sensor)
            <a href="{{ route('sensors.show', $sensor) }}" style="display: block; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px; text-decoration: none; transition: border-color 0.2s;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <div style="font-weight: 700; color: var(--text-main);">{{ $sensor->name }}</div>
                    <div style="font-size: 0.75rem; font-weight: 600; padding: 0.1rem 0.5rem; background: {{ $sensor->status === 'online' ? '#dcfce7' : '#fee2e2' }}; color: {{ $sensor->status === 'online' ? '#166534' : '#991b1b' }}; border-radius: 999px;">{{ ucfirst($sensor->status) }}</div>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: capitalize;">{{ str_replace('_', ' ', $sensor->type) }}</div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if($alerts->isNotEmpty())
    <div>
        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;"><i class="fas fa-bell text-yellow-500 mr-2"></i> Alerts</h3>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($alerts as $alert)
            <div style="display: flex; align-items: flex-start; gap: 1rem; padding: 1rem; border: 1px solid var(--border-color); border-radius: 8px;">
                <i class="fas {{ in_array($alert->type, ['critical', 'warning']) ? 'fa-exclamation-triangle' : 'fa-info-circle' }}" style="color: {{ in_array($alert->type, ['critical', 'warning']) ? '#f97316' : '#3b82f6' }}; font-size: 1.25rem; margin-top: 2px;"></i>
                <div>
                    <div style="font-weight: 700; color: var(--text-main); margin-bottom: 0.25rem; font-size: 0.95rem;">{{ $alert->title }}</div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">{{ $alert->message }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem;">{{ $alert->created_at->format('M d, Y H:i') }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
