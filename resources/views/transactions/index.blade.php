<x-app-layout title="Sales & Rentals">
    <div class="heading"><div><h1>{{ __('Sales & rentals') }}</h1><p>{{ __('Al Ramz agreements, deposits, collections, and handovers.') }}</p></div></div>
    @if(session('success'))<div class="notice">{{ __(session('success')) }}</div>@endif
    <div class="grid">
        <section class="card">
            <h2>{{ __('Transactions') }}</h2>
            @if($transactions->isEmpty())
                <div class="empty">{{ __('No transactions yet. Create the first sale or rental agreement.') }}</div>
            @else
                <table><thead><tr><th>{{ __('Reference') }}</th><th>{{ __('Property') }}</th><th>{{ __('Value') }}</th><th>{{ __('Collected') }}</th><th>{{ __('Status') }}</th></tr></thead><tbody>
                @foreach($transactions as $transaction)
                    <tr><td><strong>{{ $transaction->reference }}</strong><br><small>{{ $transaction->client?->name }}</small></td><td>{{ $transaction->property->title }}</td><td>OMR {{ number_format($transaction->agreed_value, 3) }}</td><td>OMR {{ number_format($transaction->payments->where('status', 'approved')->sum('amount'), 3) }}<br><small>{{ __('Outstanding') }}: OMR {{ number_format(max(0, $transaction->agreed_value - $transaction->payments->where('status', 'approved')->sum('amount')), 3) }}</small></td><td><span class="tag">{{ __(str($transaction->status)->title()->toString()) }}</span></td></tr>
                    @foreach($transaction->payments->where('status', 'pending') as $payment)
                        <tr><td colspan="5"><form method="POST" action="{{ route('payments.approve', $payment) }}">@csrf @method('PATCH')<strong>{{ __('Pending payment') }}:</strong> OMR {{ number_format($payment->amount, 3) }} {{ __('by') }} {{ __(str($payment->method)->replace('_', ' ')->title()->toString()) }} @if(in_array($companyRoles[$transaction->company_id] ?? null, ['admin', 'manager', 'finance'], true))<button class="tag" type="submit">{{ __('Approve payment') }}</button>@endif</form></td></tr>
                    @endforeach
                    <tr><td colspan="5"><form method="POST" action="{{ route('transactions.payments.store', $transaction) }}">@csrf<div class="two"><div class="field"><label>{{ __('Record payment (OMR)') }}</label><input name="amount" type="number" min="0.001" step="0.001" required></div><div class="field"><label>{{ __('Method') }}</label><select name="method"><option value="cash">{{ __('Cash') }}</option><option value="cheque">{{ __('Cheque') }}</option><option value="bank_transfer">{{ __('Bank transfer') }}</option><option value="card_terminal">{{ __('Card terminal') }}</option></select></div></div><div class="two"><div class="field"><label>{{ __('Received on') }}</label><input name="received_on" type="date" value="{{ now()->toDateString() }}" required></div><div class="field"><label>&nbsp;</label><button class="btn">{{ __('Record for approval') }}</button></div></div></form></td></tr>
                @endforeach
                </tbody></table>{{ $transactions->links() }}
            @endif
        </section>
        <aside class="card"><h2>{{ __('New transaction') }}</h2><form method="POST" action="{{ route('transactions.store') }}">@csrf
            <div class="field"><label>{{ __('Company') }}</label><select name="company_id" required><option value="">{{ __('Select company') }}</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->name }}</option>@endforeach</select></div>
            <div class="field"><label>{{ __('Property') }}</label><select name="property_id" required><option value="">{{ __('Select property') }}</option>@foreach($properties as $property)<option value="{{ $property->id }}">{{ $property->reference }} — {{ $property->title }}</option>@endforeach</select></div>
            <div class="field"><label>{{ __('Client') }}</label><select name="client_id"><option value="">{{ __('Select client') }}</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }}</option>@endforeach</select></div>
            <div class="two"><div class="field"><label>{{ __('Type') }}</label><select name="type"><option value="sale">{{ __('Sale') }}</option><option value="rental">{{ __('Rental') }}</option></select></div><div class="field"><label>{{ __('Agreed value (OMR)') }}</label><input name="agreed_value" type="number" min="0" step="0.001" required></div></div>
            <div class="two"><div class="field"><label>{{ __('Agreement date') }}</label><input name="agreement_date" type="date"></div><div class="field"><label>{{ __('Deposit (OMR)') }}</label><input name="deposit_amount" type="number" min="0" step="0.001"></div></div>
            <button class="btn">{{ __('Create draft transaction') }}</button>
        </form></aside>
    </div>
</x-app-layout>
