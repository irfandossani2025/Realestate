<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->orderBy('name')->get();
        $properties = Property::query()
            ->whereIn('company_id', $companies->modelKeys())
            ->with(['company', 'owner'])
            ->latest()
            ->paginate(15);

        return view('properties.index', compact('companies', 'properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id')->all();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($companyIds)],
            'title' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'property_type' => ['required', Rule::in(['apartment', 'villa', 'land', 'office', 'retail', 'warehouse', 'building'])],
            'purpose' => ['required', Rule::in(['sale', 'rent', 'both'])],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['nullable', 'string', 'max:100'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:99'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:99'],
            'area_sqm' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'rent_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        Property::query()->create([
            ...$data,
            'reference' => 'RE-'.now()->format('ymd').'-'.str_pad((string) (Property::count() + 1), 4, '0', STR_PAD_LEFT),
            'status' => 'draft',
            'publication_status' => 'internal',
        ]);

        return redirect()->route('properties.index')->with('success', 'Property saved as an internal draft.');
    }
}
