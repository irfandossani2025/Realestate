<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->orderBy('name')->get();
        $leads = Lead::query()
            ->whereIn('company_id', $companies->modelKeys())
            ->with(['company', 'contact', 'property', 'assignee'])
            ->latest()
            ->paginate(15);

        return view('leads.index', compact('companies', 'leads'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id')->all();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($companyIds)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'source' => ['required', Rule::in(['manual', 'phone', 'email', 'walk_in', 'website', 'whatsapp', 'referral'])],
            'interest_type' => ['nullable', Rule::in(['sale', 'rent', 'valuation'])],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $contact = $request->user()->companies()->findOrFail($data['company_id'])->contacts()->firstOrCreate(
            ['email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null, 'name' => $data['name']],
            ['type' => 'client']
        );

        Lead::query()->create([
            ...$data,
            'contact_id' => $contact->id,
            'reference' => 'LD-'.now()->format('ymd').'-'.str_pad((string) (Lead::count() + 1), 4, '0', STR_PAD_LEFT),
            'status' => 'new',
            'assigned_to' => $request->user()->id,
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead added and assigned to you.');
    }
}
