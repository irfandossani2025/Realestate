<?php

namespace App\Http\Controllers;

use App\Models\ValuationRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ValuationRequestController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->where('business_type', 'valuation')->wherePivot('is_active', true)->get();
        $requests = ValuationRequest::query()->whereIn('company_id', $companies->modelKeys())->with(['company', 'contact', 'property', 'evaluator'])->latest()->paginate(15);

        return view('valuations.index', compact('companies', 'requests'));
    }

    public function store(Request $request)
    {
        $ids = $request->user()->companies()->where('business_type', 'valuation')->wherePivot('is_active', true)->pluck('companies.id')->all();
        $data = $request->validate(['company_id' => ['required', Rule::in($ids)], 'purpose' => ['required', Rule::in(['sale', 'mortgage', 'insurance', 'legal', 'internal'])], 'fee' => ['nullable', 'numeric', 'min:0'], 'report_due_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string', 'max:3000']]);
        ValuationRequest::query()->create([...$data, 'reference' => 'VAL-'.now()->format('ymd').'-'.str_pad((string) (ValuationRequest::count() + 1), 4, '0', STR_PAD_LEFT), 'status' => 'requested', 'evaluator_id' => $request->user()->id]);

        return redirect()->route('valuations.index')->with('success', 'Valuation request created.');
    }
}
