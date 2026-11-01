<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\PropertyTransaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->get();
        $companyIds = $companies->modelKeys();
        $commissions = Commission::query()->whereIn('company_id', $companyIds)->with(['transaction.property', 'agent'])->latest()->paginate(15);
        $transactions = PropertyTransaction::query()->whereIn('company_id', $companyIds)->with('property')->latest()->get();
        $agents = User::query()->whereHas('companies', fn ($query) => $query->whereIn('companies.id', $companyIds))->orderBy('name')->get();

        return view('commissions.index', compact('agents', 'commissions', 'companies', 'transactions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $memberships = $request->user()->companies()->wherePivot('is_active', true)->get();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($memberships->modelKeys())], 'property_transaction_id' => ['required', 'integer'],
            'user_id' => ['required', 'integer'], 'calculation_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'rate' => ['required', 'numeric', 'min:0'],
        ]);
        $membership = $memberships->firstWhere('id', (int) $data['company_id']);
        abort_unless($membership && in_array($membership->pivot->role, ['admin', 'manager', 'finance'], true), 403);
        $transaction = PropertyTransaction::query()->where('company_id', $membership->id)->findOrFail($data['property_transaction_id']);
        User::query()->whereHas('companies', fn ($query) => $query->where('companies.id', $membership->id))->findOrFail($data['user_id']);
        $basis = (float) $transaction->agreed_value;
        $amount = $data['calculation_type'] === 'percentage' ? round($basis * (float) $data['rate'] / 100, 3) : round((float) $data['rate'], 3);
        Commission::query()->create([...$data, 'basis_amount' => $basis, 'amount' => $amount, 'status' => 'pending']);

        return redirect()->route('commissions.index')->with('success', 'Commission calculated and awaiting approval.');
    }
}
