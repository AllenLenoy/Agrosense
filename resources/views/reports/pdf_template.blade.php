<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Compliance Report - {{ $farm->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #059669; padding-bottom: 10px; }
        .header h1 { color: #059669; margin: 0; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 10px; text-transform: uppercase; }
        .section-title { font-size: 14px; color: #059669; border-bottom: 1px solid #ddd; margin-top: 30px; margin-bottom: 15px; padding-bottom: 5px; font-weight: bold; text-transform: uppercase; }
        .info-grid { width: 100%; margin-bottom: 20px; }
        .info-grid td { padding: 5px 0; }
        .info-grid .label { font-weight: bold; width: 30%; color: #555; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; }
        table.data-table th { background-color: #f8fafc; color: #334155; font-size: 11px; text-transform: uppercase; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; color: white; }
        .badge-online { background-color: #10b981; }
        .badge-offline { background-color: #ef4444; }
        .badge-critical { background-color: #ef4444; }
        .badge-warning { background-color: #f59e0b; }
        .badge-resolved { background-color: #10b981; }
        .footer { position: absolute; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; padding-top: 10px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>

    <div class="header">
        <h1>AgroSense Compliance Report</h1>
        <p>Generated on {{ now()->format('F j, Y H:i:s') }}</p>
    </div>

    <div class="section-title">Farm Details</div>
    <table class="info-grid">
        <tr>
            <td class="label">Farm Name:</td>
            <td>{{ $farm->name }}</td>
            <td class="label">Owner:</td>
            <td>{{ $farm->user->name }}</td>
        </tr>
        <tr>
            <td class="label">Location:</td>
            <td>{{ $farm->location ?? 'Not Specified' }}</td>
            <td class="label">Total Area:</td>
            <td>{{ $farm->area_acres ?? 'N/A' }} acres</td>
        </tr>
    </table>

    <div class="section-title">Active IoT Sensors</div>
    @if($sensors->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th>Device ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Status</th>
                <th>Last Reading</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sensors as $sensor)
            <tr>
                <td style="font-family: monospace;">{{ $sensor->device_id }}</td>
                <td>{{ $sensor->name }}</td>
                <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $sensor->type) }}</td>
                <td>
                    <span class="badge {{ $sensor->status === 'online' ? 'badge-online' : 'badge-offline' }}">
                        {{ strtoupper($sensor->status) }}
                    </span>
                </td>
                <td>{{ $sensor->last_reading_at ? $sensor->last_reading_at->format('Y-m-d H:i') : 'Never' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No IoT sensors currently registered to this farm.</p>
    @endif

    <div class="section-title">Recent Critical Alerts (Last 20)</div>
    @if($recentAlerts->count() > 0)
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Message</th>
                <th>Severity</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentAlerts as $alert)
            <tr>
                <td>{{ $alert->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $alert->message }}</td>
                <td>
                    <span class="badge badge-{{ $alert->severity ?? $alert->type }}">
                        {{ strtoupper($alert->severity ?? $alert->type ?? 'info') }}
                    </span>
                </td>
                <td>{{ $alert->is_resolved ? 'Resolved' : 'Active' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>No recent alerts recorded.</p>
    @endif


    <div class="footer">
        AgroSense Platform &copy; {{ date('Y') }} &middot; Official Compliance Record &middot; Page 1
    </div>

</body>
</html>
