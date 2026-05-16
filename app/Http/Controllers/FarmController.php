<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Field;
use App\Models\Crop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FarmController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $farms = $user->isAdmin()
            ? Farm::with(['user', 'fields', 'sensors'])->paginate(12)
            : $user->farms()->with(['fields', 'sensors'])->paginate(12);

        return view('farms.index', compact('farms'));
    }

    public function show(Farm $farm)
    {
        $farm->load(['fields.crops', 'sensors', 'alerts' => fn($q) => $q->latest()->take(5)]);

        return view('farms.show', compact('farm'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'area_acres' => 'nullable|numeric|min:0',
            'soil_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        Auth::user()->farms()->create($request->all());

        return back()->with('success', 'Farm created successfully.');
    }

    public function update(Request $request, Farm $farm)
    {
        if (!Auth::user()->isAdmin() && Auth::id() !== $farm->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'area_acres' => 'nullable|numeric|min:0',
            'soil_type' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        $farm->update($request->all());

        return back()->with('success', 'Farm updated successfully.');
    }

    public function destroy(Farm $farm)
    {
        if (!Auth::user()->isAdmin() && Auth::id() !== $farm->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $farm->delete();
        return redirect()->route('farms.index')->with('success', 'Farm deleted successfully.');
    }
}
