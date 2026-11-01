<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceRequest;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->get();
        $companyIds = $companies->modelKeys();
        $requests = MaintenanceRequest::query()->whereIn('company_id', $companyIds)->with(['company', 'property', 'assignee'])->latest()->paginate(15);
        $properties = Property::query()->whereIn('company_id', $companyIds)->orderBy('title')->get();

        return view('maintenance.index', compact('companies', 'properties', 'requests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id')->all();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($companyIds)], 'property_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'], 'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high', 'urgent'])],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'], 'scheduled_at' => ['nullable', 'date'],
        ]);
        $data['property_id'] = Property::query()->where('company_id', $data['company_id'])->findOrFail($data['property_id'])->id;
        MaintenanceRequest::query()->create([...$data, 'reference' => 'MNT-'.now()->format('ymd').'-'.Str::upper(Str::random(6)), 'status' => 'open']);

        return redirect()->route('maintenance.index')->with('success', 'Maintenance request opened.');
    }
}
