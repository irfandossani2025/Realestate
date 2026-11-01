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
        $companyRoles = $companies->pluck('pivot.role', 'id');
        $properties = Property::query()
            ->whereIn('company_id', $companies->modelKeys())
            ->with(['company', 'owner'])
            ->latest()
            ->paginate(15);

        return view('properties.index', compact('companies', 'companyRoles', 'properties'));
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

    public function updatePublication(Request $request, Property $property): RedirectResponse
    {
        $membership = $request->user()->companies()
            ->where('companies.id', $property->company_id)
            ->wherePivot('is_active', true)
            ->first();
        abort_unless($membership, 403);

        $data = $request->validate([
            'publication_status' => ['required', Rule::in(['internal', 'review', 'approved', 'published', 'hidden'])],
        ]);
        $managerStates = ['approved', 'published', 'hidden'];
        if (in_array($data['publication_status'], $managerStates, true)) {
            abort_unless(in_array($membership->pivot->role, ['admin', 'manager', 'marketing_manager'], true), 403);
        }

        $property->update([
            'publication_status' => $data['publication_status'],
            'published_at' => $data['publication_status'] === 'published' ? now() : null,
        ]);

        return redirect()->route('properties.index')->with('success', 'Publication status updated.');
    }
}
