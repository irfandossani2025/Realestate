<x-app-layout title="Invoices">
    <div class="heading"><div><h1>Invoices</h1><p>OMR invoices with configurable Oman VAT and bilingual descriptions.</p></div></div>
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    <div class="grid"><section class="card"><h2>Recent invoices</h2>
        @if($invoices->isEmpty())<div class="empty">No invoices yet.</div>@else<table><thead><tr><th>Invoice</th><th>Company</th><th>Client</th><th>Total</th><th>Status</th></tr></thead><tbody>@foreach($invoices as $invoice)<tr><td><strong>{{ $invoice->invoice_number }}</strong><br><small>{{ $invoice->issue_date }}</small></td><td>{{ $invoice->company->name }}</td><td>{{ $invoice->contact?->name }}</td><td>OMR {{ number_format($invoice->total, 3) }}<br><small>VAT {{ number_format($invoice->vat_amount, 3) }}</small></td><td><span class="tag">{{ str($invoice->status)->title() }}</span></td></tr>@endforeach</tbody></table>{{ $invoices->links() }}@endif
    </section><aside class="card"><h2>Create draft invoice</h2><form method="POST" action="{{ route('invoices.store') }}">@csrf
        <div class="field"><label>Company</label><select name="company_id" required><option value="">Select company</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->name }}</option>@endforeach</select></div>
        <div class="field"><label>Client</label><select name="contact_id"><option value="">Select client</option>@foreach($contacts as $contact)<option value="{{ $contact->id }}">{{ $contact->name }}</option>@endforeach</select></div>
        <div class="two"><div class="field"><label>Issue date</label><input name="issue_date" type="date" value="{{ now()->toDateString() }}" required></div><div class="field"><label>Due date</label><input name="due_date" type="date"></div></div>
        <div class="field"><label>Description</label><input name="description" required></div><div class="field"><label>Arabic description</label><input name="description_ar" dir="rtl"></div>
        <div class="two"><div class="field"><label>Quantity</label><input name="quantity" type="number" min="0.001" step="0.001" value="1" required></div><div class="field"><label>Unit price (OMR)</label><input name="unit_price" type="number" min="0" step="0.001" required></div></div>
        <div class="field"><label>VAT rate (%)</label><input name="vat_rate" type="number" min="0" max="100" step="0.01" placeholder="Company default"></div><button class="btn">Create draft invoice</button>
    </form></aside></div>
</x-app-layout>
