<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Property;
use App\Models\ValuationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ValuationRequestController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->where('business_type', 'valuation')->wherePivot('is_active', true)->get();
        $accessibleCompanyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $requests = ValuationRequest::query()->whereIn('company_id', $companies->modelKeys())->with(['company', 'contact', 'property', 'evaluator'])->latest()->paginate(15);
        $properties = Property::query()->whereIn('company_id', $accessibleCompanyIds)->orderBy('title')->get();
        $contacts = Contact::query()->whereIn('company_id', $accessibleCompanyIds)->orderBy('name')->get();

        return view('valuations.index', compact('companies', 'contacts', 'properties', 'requests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $ids = $request->user()->companies()->where('business_type', 'valuation')->wherePivot('is_active', true)->pluck('companies.id')->all();
        $accessibleCompanyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $data = $request->validate([
            'company_id' => ['required', Rule::in($ids)], 'property_id' => ['nullable', 'integer'], 'contact_id' => ['nullable', 'integer'],
            'purpose' => ['required', Rule::in(['sale', 'mortgage', 'insurance', 'legal', 'internal'])],
            'fee' => ['nullable', 'numeric', 'min:0'], 'inspection_at' => ['nullable', 'date'],
            'report_due_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        if (! empty($data['property_id'])) {
            $data['property_id'] = Property::query()->whereIn('company_id', $accessibleCompanyIds)->findOrFail($data['property_id'])->id;
        }
        if (! empty($data['contact_id'])) {
            $data['contact_id'] = Contact::query()->whereIn('company_id', $accessibleCompanyIds)->findOrFail($data['contact_id'])->id;
        }
        ValuationRequest::query()->create([...$data, 'reference' => 'VAL-'.now()->format('ymd').'-'.Str::upper(Str::random(6)), 'status' => 'requested', 'evaluator_id' => $request->user()->id]);

        return redirect()->route('valuations.index')->with('success', 'Valuation request created.');
    }

    public function updateStatus(Request $request, ValuationRequest $valuation): RedirectResponse
    {
        $membership = $request->user()->companies()->where('companies.id', $valuation->company_id)->wherePivot('is_active', true)->first();
        abort_unless($membership && in_array($membership->pivot->role, ['admin', 'manager', 'evaluator'], true), 403);
        $data = $request->validate(['status' => ['required', Rule::in(['requested', 'scheduled', 'inspected', 'drafting', 'review', 'completed', 'cancelled'])]]);
        $valuation->update(['status' => $data['status'], 'completed_at' => $data['status'] === 'completed' ? now() : null]);

        return redirect()->route('valuations.index')->with('success', 'Valuation status updated.');
    }
}
