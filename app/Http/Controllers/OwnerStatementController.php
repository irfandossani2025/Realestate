<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Contact;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OwnerStatementController extends Controller
{
    public function index(Request $request): View
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id');
        $owners = Contact::query()->whereIn('company_id', $companyIds)->where('type', 'owner')->with('ownedProperties')->orderBy('name')->get();
        $statements = $owners->map(fn (Contact $owner) => $this->statement($owner, $companyIds));

        return view('owners.statements', compact('statements'));
    }

    private function statement(Contact $owner, Collection $companyIds): array
    {
        $propertyIds = $owner->ownedProperties->modelKeys();
        $collections = (float) Payment::query()->whereIn('company_id', $companyIds)->where('status', 'approved')->whereHas('transaction', fn ($query) => $query->whereIn('property_id', $propertyIds))->sum('amount');
        $maintenance = (float) MaintenanceRequest::query()->whereIn('company_id', $companyIds)->whereIn('property_id', $propertyIds)->sum('actual_cost');
        $commissions = (float) Commission::query()->whereIn('company_id', $companyIds)->whereIn('status', ['approved', 'paid'])->whereHas('transaction', fn ($query) => $query->whereIn('property_id', $propertyIds))->sum('amount');

        return ['owner' => $owner, 'properties' => count($propertyIds), 'collections' => $collections, 'maintenance' => $maintenance, 'commissions' => $commissions, 'net' => $collections - $maintenance - $commissions];
    }
}
