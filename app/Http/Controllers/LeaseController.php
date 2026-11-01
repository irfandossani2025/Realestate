<?php

namespace App\Http\Controllers;

use App\Models\Lease;
use App\Models\PropertyTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaseController extends Controller
{
    public function index(Request $request): View
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $leases = Lease::query()->whereIn('company_id', $companyIds)->with(['transaction.property', 'transaction.client'])->orderBy('ends_on')->paginate(15);
        $transactions = PropertyTransaction::query()->whereIn('company_id', $companyIds)->where('type', 'rental')->whereDoesntHave('lease')->with('property')->get();

        return view('leases.index', compact('leases', 'transactions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $data = $request->validate([
            'property_transaction_id' => ['required', 'integer'], 'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'], 'rent_amount' => ['required', 'numeric', 'gt:0'],
            'rent_frequency' => ['required', Rule::in(['monthly', 'quarterly', 'semi_annual', 'annual'])],
            'security_deposit' => ['nullable', 'numeric', 'min:0'], 'renewal_notice_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);
        $transaction = PropertyTransaction::query()->whereIn('company_id', $companyIds)->where('type', 'rental')->findOrFail($data['property_transaction_id']);
        Lease::query()->create([
            ...$data, 'company_id' => $transaction->company_id,
            'reference' => 'LSE-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'security_deposit' => $data['security_deposit'] ?? 0, 'next_payment_due_on' => $data['starts_on'],
            'renewal_status' => 'not_due', 'status' => 'active',
        ]);

        return redirect()->route('leases.index')->with('success', 'Lease created and renewal tracking started.');
    }
}
