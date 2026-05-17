<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Field;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function store(Request $request, Farm $farm)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'area_acres'   => 'required|numeric|min:0',
            'soil_type'    => 'required|string',
            'current_crop' => 'nullable|string|max:255',
            'status'       => 'nullable|in:active,preparing,fallow',
            'geojson'      => 'nullable|string',
        ]);

        // Ensure user can manage this farm
        if (!$request->user()->isAdmin() && !$request->user()->farms()->where('farms.id', $farm->id)->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $geojsonArray = null;
        if ($request->filled('geojson')) {
            $geojsonArray = json_decode($request->geojson, true);
        }

        $farm->fields()->create([
            'name'         => $request->name,
            'area_acres'   => $request->area_acres,
            'soil_type'    => $request->soil_type,
            'current_crop' => $request->current_crop ?: null,
            'status'       => $request->status ?? 'active',
            'geojson'      => $geojsonArray,
        ]);

        return redirect()->route('farms.show', $farm)->with('success', 'Field added successfully.');
    }
}
