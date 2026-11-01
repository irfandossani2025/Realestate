<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $companies = Company::query()
            ->whereIn('id', $companyIds)
            ->withCount([
                'properties',
                'leads as open_leads_count' => fn ($query) => $query->whereNotIn('status', ['converted', 'lost']),
            ])
            ->withSum('transactions as transaction_value', 'agreed_value')
            ->withSum(['payments as approved_collections' => fn ($query) => $query->where('status', 'approved')], 'amount')
            ->withSum('invoices as invoice_total', 'total')
            ->withSum('invoices as vat_total', 'vat_amount')
            ->orderBy('name')
            ->get();

        $totals = $this->totals($companies);

        return view('reports.index', compact('companies', 'totals'));
    }

    /** @param Collection<int, Company> $companies */
    private function totals(Collection $companies): array
    {
        $transactionValue = (float) $companies->sum('transaction_value');
        $collections = (float) $companies->sum('approved_collections');

        return [
            'properties' => $companies->sum('properties_count'),
            'open_leads' => $companies->sum('open_leads_count'),
            'transaction_value' => $transactionValue,
            'collections' => $collections,
            'outstanding' => max(0, $transactionValue - $collections),
            'invoice_total' => (float) $companies->sum('invoice_total'),
            'vat_total' => (float) $companies->sum('vat_total'),
        ];
    }
}
