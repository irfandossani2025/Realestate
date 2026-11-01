<x-app-layout title="Reports">
    <div class="heading"><div><h1>Management reports</h1><p>Live operational and financial position across your assigned companies.</p></div></div>
    <section class="overview" style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px">
        <div class="card"><small>Properties</small><h2>{{ $totals['properties'] }}</h2></div>
        <div class="card"><small>Open leads</small><h2>{{ $totals['open_leads'] }}</h2></div>
        <div class="card"><small>Approved collections</small><h2>OMR {{ number_format($totals['collections'], 3) }}</h2></div>
        <div class="card"><small>Outstanding</small><h2>OMR {{ number_format($totals['outstanding'], 3) }}</h2></div>
    </section>
    <section class="card"><h2>Company summary</h2>
        @if($companies->isEmpty())<div class="empty">No company access has been assigned.</div>@else
        <table><thead><tr><th>Company</th><th>Properties</th><th>Open leads</th><th>Transactions</th><th>Collected</th><th>Outstanding</th><th>Invoice VAT</th></tr></thead><tbody>
            @foreach($companies as $company)
                @php($outstanding = max(0, (float) $company->transaction_value - (float) $company->approved_collections))
                <tr><td><strong>{{ $company->name }}</strong><br><small>{{ $company->business_type }}</small></td><td>{{ $company->properties_count }}</td><td>{{ $company->open_leads_count }}</td><td>OMR {{ number_format($company->transaction_value ?? 0, 3) }}</td><td>OMR {{ number_format($company->approved_collections ?? 0, 3) }}</td><td>OMR {{ number_format($outstanding, 3) }}</td><td>OMR {{ number_format($company->vat_total ?? 0, 3) }}</td></tr>
            @endforeach
        </tbody></table>@endif
    </section>
</x-app-layout>
