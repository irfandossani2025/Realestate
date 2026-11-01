<x-app-layout title="Sales & Rentals">
    <div class="heading"><div><h1>Sales & rentals</h1><p>Al Ramz agreements, deposits, collections, and handovers.</p></div></div>
    @if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
    <div class="grid">
        <section class="card">
            <h2>Transactions</h2>
            @if($transactions->isEmpty())
                <div class="empty">No transactions yet. Create the first sale or rental agreement.</div>
            @else
                <table><thead><tr><th>Reference</th><th>Property</th><th>Value</th><th>Collected</th><th>Status</th></tr></thead><tbody>
                @foreach($transactions as $transaction)
                    <tr><td><strong>{{ $transaction->reference }}</strong><br><small>{{ $transaction->client?->name }}</small></td><td>{{ $transaction->property->title }}</td><td>OMR {{ number_format($transaction->agreed_value, 3) }}</td><td>OMR {{ number_format($transaction->payments->where('status', 'approved')->sum('amount'), 3) }}</td><td><span class="tag">{{ str($transaction->status)->title() }}</span></td></tr>
                    <tr><td colspan="5"><form method="POST" action="{{ route('transactions.payments.store', $transaction) }}">@csrf<div class="two"><div class="field"><label>Record payment (OMR)</label><input name="amount" type="number" min="0.001" step="0.001" required></div><div class="field"><label>Method</label><select name="method"><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="bank_transfer">Bank transfer</option><option value="card_terminal">Card terminal</option></select></div></div><div class="two"><div class="field"><label>Received on</label><input name="received_on" type="date" value="{{ now()->toDateString() }}" required></div><div class="field"><label>&nbsp;</label><button class="btn">Record for approval</button></div></div></form></td></tr>
                @endforeach
                </tbody></table>{{ $transactions->links() }}
            @endif
        </section>
        <aside class="card"><h2>New transaction</h2><form method="POST" action="{{ route('transactions.store') }}">@csrf
            <div class="field"><label>Company</label><select name="company_id" required><option value="">Select company</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->name }}</option>@endforeach</select></div>
            <div class="field"><label>Property</label><select name="property_id" required><option value="">Select property</option>@foreach($properties as $property)<option value="{{ $property->id }}">{{ $property->reference }} — {{ $property->title }}</option>@endforeach</select></div>
            <div class="field"><label>Client</label><select name="client_id"><option value="">Select client</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select></div>
            <div class="two"><div class="field"><label>Type</label><select name="type"><option value="sale">Sale</option><option value="rental">Rental</option></select></div><div class="field"><label>Agreed value (OMR)</label><input name="agreed_value" type="number" min="0" step="0.001" required></div></div>
            <div class="two"><div class="field"><label>Agreement date</label><input name="agreement_date" type="date"></div><div class="field"><label>Deposit (OMR)</label><input name="deposit_amount" type="number" min="0" step="0.001"></div></div>
            <button class="btn">Create draft transaction</button>
        </form></aside>
    </div>
</x-app-layout>
