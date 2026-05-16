<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Farm;
use App\Models\SensorReading;
use App\Models\Alert;
use App\Exports\SensorReadingExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $farms = $user->isAdmin() 
            ? Farm::all() 
            : $user->farms;

        return view('reports.index', compact('farms'));
    }

    public function exportCsv(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $farm = Farm::findOrFail($request->farm_id);
        
        // Ensure user can access this farm
        if (!$request->user()->isAdmin() && !$request->user()->farms()->where('farms.id', $farm->id)->exists()) {
            abort(403);
        }

        $filename = 'sensor_data_' . $farm->id . '_' . now()->format('Ymd_Hi') . '.csv';
        return Excel::download(new SensorReadingExport($farm->id, $request->start_date, $request->end_date), $filename);
    }

    public function exportPdf(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
        ]);

        $farm = Farm::findOrFail($request->farm_id);
        
        // Ensure user can access this farm
        if (!$request->user()->isAdmin() && !$request->user()->farms()->where('farms.id', $farm->id)->exists()) {
            abort(403);
        }

        $sensors = $farm->sensors()->with('latestReading')->get();
        $recentAlerts = Alert::where('farm_id', $farm->id)
            ->latest()
            ->take(20)
            ->get();
            
        $diseases = $farm->diseases()->latest()->take(10)->get();

        $pdf = Pdf::loadView('reports.pdf_template', compact('farm', 'sensors', 'recentAlerts', 'diseases'));
        
        return $pdf->download('farm_compliance_report_' . $farm->id . '_' . now()->format('Ymd') . '.pdf');
    }
}
