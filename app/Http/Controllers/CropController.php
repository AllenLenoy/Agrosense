<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CropController extends Controller
{
    public function store(Request $request, Field $field)
    {
        if (!Auth::user()->isAdmin() && $field->farm->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'health_score' => 'required|integer|min:0|max:100',
        ]);

        $field->crops()->create($request->only('name', 'health_score'));

        return back()->with('success', 'Crop added successfully.');
    }

    public function update(Request $request, Crop $crop)
    {
        if (!Auth::user()->isAdmin() && $crop->field->farm->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'health_score' => 'required|integer|min:0|max:100',
        ]);

        $crop->update($request->only('name', 'health_score'));

        return back()->with('success', 'Crop updated successfully.');
    }

    public function destroy(Crop $crop)
    {
        if (!Auth::user()->isAdmin() && $crop->field->farm->user_id !== Auth::id()) {
            abort(403);
        }

        $crop->delete();

        return back()->with('success', 'Crop removed successfully.');
    }
}
