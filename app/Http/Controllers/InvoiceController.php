<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Invoice;
use App\Services\InvoiceCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->get();
        $companyIds = $companies->modelKeys();
        $invoices = Invoice::query()->whereIn('company_id', $companyIds)->with(['company', 'contact'])->latest()->paginate(15);
        $contacts = Contact::query()->whereIn('company_id', $companyIds)->orderBy('name')->get();

        return view('invoices.index', compact('companies', 'contacts', 'invoices'));
    }

    public function store(Request $request, InvoiceCalculator $calculator): RedirectResponse
    {
        $memberships = $request->user()->companies()->wherePivot('is_active', true)->get();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($memberships->modelKeys())],
            'contact_id' => ['nullable', 'integer'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'description' => ['required', 'string', 'max:255'],
            'description_ar' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);
        $membership = $memberships->firstWhere('id', (int) $data['company_id']);
        abort_unless($membership && in_array($membership->pivot->role, ['admin', 'manager', 'finance'], true), 403);
        if (! empty($data['contact_id'])) {
            $data['contact_id'] = Contact::query()->where('company_id', $membership->id)->findOrFail($data['contact_id'])->id;
        }
        $vatRate = (float) ($data['vat_rate'] ?? $membership->default_vat_rate);
        $amounts = $calculator->calculate((float) $data['quantity'], (float) $data['unit_price'], $vatRate);

        DB::transaction(function () use ($data, $membership, $vatRate, $amounts): void {
            $invoice = Invoice::query()->create([
                'company_id' => $membership->id, 'contact_id' => $data['contact_id'] ?? null,
                'invoice_number' => 'INV-'.now()->format('ymd').'-'.Str::upper(Str::random(6)), 'status' => 'draft',
                'issue_date' => $data['issue_date'], 'due_date' => $data['due_date'] ?? null, ...$amounts,
            ]);
            $invoice->lines()->create([
                'description' => $data['description'], 'description_ar' => $data['description_ar'] ?? null,
                'quantity' => $data['quantity'], 'unit_price' => $data['unit_price'], 'vat_rate' => $vatRate, ...$amounts,
            ]);
        });

        return redirect()->route('invoices.index')->with('success', 'Draft invoice created.');
    }
}
