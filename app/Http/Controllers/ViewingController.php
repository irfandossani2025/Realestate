<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Property;
use App\Models\Viewing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ViewingController extends Controller
{
    public function index(Request $request): View
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $viewings = Viewing::query()->whereIn('company_id', $companyIds)->with(['property', 'contact', 'assignee'])->orderBy('scheduled_at')->paginate(20);
        $properties = Property::query()->whereIn('company_id', $companyIds)->orderBy('title')->get();
        $contacts = Contact::query()->whereIn('company_id', $companyIds)->orderBy('name')->get();

        return view('viewings.index', compact('contacts', 'properties', 'viewings'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $data = $request->validate(['property_id' => ['required', 'integer'], 'contact_id' => ['nullable', 'integer'], 'scheduled_at' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:3000']]);
        $property = Property::query()->whereIn('company_id', $companyIds)->findOrFail($data['property_id']);
        if (! empty($data['contact_id'])) {
            $data['contact_id'] = Contact::query()->whereIn('company_id', $companyIds)->findOrFail($data['contact_id'])->id;
        }
        Viewing::query()->create([...$data, 'company_id' => $property->company_id, 'assigned_to' => $request->user()->id, 'reference' => 'VEW-'.now()->format('ymd').'-'.Str::upper(Str::random(6)), 'status' => 'scheduled']);

        return redirect()->route('viewings.index')->with('success', 'Viewing scheduled.');
    }

    public function update(Request $request, Viewing $viewing): RedirectResponse
    {
        abort_unless($request->user()->companies()->where('companies.id', $viewing->company_id)->wherePivot('is_active', true)->exists(), 403);
        $data = $request->validate(['status' => ['required', Rule::in(['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'])], 'outcome' => ['nullable', 'string', 'max:3000']]);
        $viewing->update($data);

        return redirect()->route('viewings.index')->with('success', 'Viewing updated.');
    }
}
