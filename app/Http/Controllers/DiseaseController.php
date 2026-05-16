<?php

namespace App\Http\Controllers;

use App\Models\DiseaseReport;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DiseaseController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $farmIds = $user->isAdmin() ? Farm::pluck('id') : $user->farms()->pluck('id');

        $reports = DiseaseReport::whereIn('farm_id', $farmIds)
            ->with(['farm', 'crop'])->latest()->paginate(10);

        $stats = [
            'total' => DiseaseReport::whereIn('farm_id', $farmIds)->count(),
            'pending' => DiseaseReport::whereIn('farm_id', $farmIds)->where('status', 'pending')->count(),
            'treated' => DiseaseReport::whereIn('farm_id', $farmIds)->where('status', 'treated')->count(),
            'critical' => DiseaseReport::whereIn('farm_id', $farmIds)->where('severity', 'critical')->count(),
        ];

        return view('diseases.index', compact('reports', 'stats'));
    }

    public function create()
    {
        $user = Auth::user();
        $farms = $user->isAdmin() ? Farm::all() : $user->farms;
        return view('diseases.create', compact('farms'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'image' => 'required|image|max:5120',
        ]);

        $path = $request->file('image')->store('diseases', 'public');

        $report = DiseaseReport::create([
            'farm_id' => $request->farm_id,
            'crop_id' => $request->crop_id,
            'image_path' => $path,
            'status' => 'pending',
        ]);

        // Simulate AI analysis
        $diseases = [
            ['name' => 'Late Blight', 'confidence' => 91.3, 'severity' => 'high',
             'description' => 'Fungal disease caused by Phytophthora infestans.',
             'treatment' => 'Apply Mancozeb 75WP at 2.5g/L.',
             'prevention' => 'Use disease-free seed. Ensure good air circulation.'],
            ['name' => 'Powdery Mildew', 'confidence' => 87.6, 'severity' => 'medium',
             'description' => 'White powdery fungal growth on upper leaf surfaces.',
             'treatment' => 'Apply Sulphur 80WP at 3g/L.',
             'prevention' => 'Plant resistant varieties. Maintain proper spacing.'],
        ];

        $selected = $diseases[array_rand($diseases)];
        $report->update(array_merge($selected, [
            'disease_name' => $selected['name'],
            'status' => 'analyzed',
        ]));

        return redirect()->route('diseases.index')->with('success', 'Disease analysis complete.');
    }
}
