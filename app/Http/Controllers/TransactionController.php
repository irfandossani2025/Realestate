<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()
            ->where('business_type', 'sales_rentals')
            ->wherePivot('is_active', true)
            ->get();
        $companyIds = $companies->modelKeys();
        $companyRoles = $companies->pluck('pivot.role', 'id');
        $transactions = PropertyTransaction::query()
            ->whereIn('company_id', $companyIds)
            ->with(['property', 'client', 'payments'])
            ->latest()
            ->paginate(15);
        $properties = Property::query()->whereIn('company_id', $companyIds)->orderBy('title')->get();
        $clients = Contact::query()->whereIn('company_id', $companyIds)->orderBy('name')->get();

        return view('transactions.index', compact('clients', 'companies', 'companyRoles', 'properties', 'transactions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()
            ->where('business_type', 'sales_rentals')
            ->wherePivot('is_active', true)
            ->pluck('companies.id')
            ->all();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($companyIds)],
            'property_id' => ['required', 'integer'],
            'client_id' => ['nullable', 'integer'],
            'type' => ['required', Rule::in(['sale', 'rental'])],
            'agreed_value' => ['required', 'numeric', 'min:0'],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
            'agreement_date' => ['nullable', 'date'],
            'handover_date' => ['nullable', 'date', 'after_or_equal:agreement_date'],
        ]);

        $data['property_id'] = Property::query()->where('company_id', $data['company_id'])->findOrFail($data['property_id'])->id;
        if (! empty($data['client_id'])) {
            $data['client_id'] = Contact::query()->where('company_id', $data['company_id'])->findOrFail($data['client_id'])->id;
        }

        PropertyTransaction::query()->create([
            ...$data,
            'reference' => 'TRX-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'status' => 'draft',
            'deposit_amount' => $data['deposit_amount'] ?? 0,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaction created as a draft.');
    }

    public function storePayment(Request $request, PropertyTransaction $transaction): RedirectResponse
    {
        $allowedCompanyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        abort_unless($allowedCompanyIds->contains($transaction->company_id), 403);

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'method' => ['required', Rule::in(['cash', 'cheque', 'bank_transfer', 'card_terminal'])],
            'received_on' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        Payment::query()->create([
            ...$data,
            'company_id' => $transaction->company_id,
            'property_transaction_id' => $transaction->id,
            'reference' => 'PAY-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'status' => 'pending',
        ]);

        return redirect()->route('transactions.index')->with('success', 'Payment recorded and awaiting approval.');
    }

    public function approvePayment(Request $request, Payment $payment): RedirectResponse
    {
        $membership = $request->user()->companies()
            ->where('companies.id', $payment->company_id)
            ->wherePivot('is_active', true)
            ->first();
        abort_unless($membership && in_array($membership->pivot->role, ['admin', 'manager', 'finance'], true), 403);

        $payment->update(['status' => 'approved']);

        return redirect()->route('transactions.index')->with('success', 'Payment approved and included in collections.');
    }
}
