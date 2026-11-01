<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Payment;
use App\Models\Property;
use App\Models\PropertyTransaction;
use App\Models\ValuationRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $activeProperties = Property::query()->whereIn('company_id', $companyIds)->whereNotIn('status', ['sold', 'rented', 'archived'])->count();
        $openLeads = Lead::query()->whereIn('company_id', $companyIds)->whereNotIn('status', ['converted', 'lost'])->count();
        $openValuations = ValuationRequest::query()->whereIn('company_id', $companyIds)->whereNotIn('status', ['completed', 'cancelled'])->count();
        $transactionValue = PropertyTransaction::query()->whereIn('company_id', $companyIds)->sum('agreed_value');
        $approvedCollections = Payment::query()->whereIn('company_id', $companyIds)->where('status', 'approved')->sum('amount');
        $outstandingCollections = max(0, (float) $transactionValue - (float) $approvedCollections);

        return view('dashboard', compact('activeProperties', 'openLeads', 'openValuations', 'outstandingCollections'));
    }
}
