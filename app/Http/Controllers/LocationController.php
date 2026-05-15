<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        $locations = Location::withCount('products')->orderBy('name')->paginate(15);
        return view('locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:locations',
            'description' => 'nullable|string',
            'building'    => 'nullable|string|max:100',
            'floor'       => 'nullable|string|max:50',
            'aisle'       => 'nullable|string|max:50',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', "Location \"{$validated['name']}\" created.");
    }

    public function edit(Location $location): View
    {
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:locations,code,' . $location->id,
            'description' => 'nullable|string',
            'building'    => 'nullable|string|max:100',
            'floor'       => 'nullable|string|max:50',
            'aisle'       => 'nullable|string|max:50',
        ]);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', "Location updated.");
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();
        return redirect()->route('locations.index')
            ->with('success', "Location deleted.");
    }
}
