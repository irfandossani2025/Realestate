<x-app-layout title="Overview">
    <div class="heading"><div><h1>{{ __('Welcome back') }}, {{ auth()->user()->name }}</h1><p>{{ __('A live view of your assigned companies and priorities.') }}</p></div></div>
    <section class="overview" style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px">
        <div class="card"><small>{{ __('Active properties') }}</small><h2 style="font-size:27px;margin:10px 0">{{ $activeProperties }}</h2><span style="color:var(--muted)">{{ __('Available in your companies') }}</span></div>
        <div class="card"><small>{{ __('Open leads') }}</small><h2 style="font-size:27px;margin:10px 0">{{ $openLeads }}</h2><span style="color:var(--muted)">{{ __('Requiring follow-up') }}</span></div>
        <div class="card"><small>{{ __('Outstanding collections') }}</small><h2 style="font-size:27px;margin:10px 0">OMR {{ number_format($outstandingCollections, 3) }}</h2><span style="color:var(--muted)">{{ __('Across active transactions') }}</span></div>
        <div class="card"><small>{{ __('Open valuations') }}</small><h2 style="font-size:27px;margin:10px 0">{{ $openValuations }}</h2><span style="color:var(--muted)">{{ __('Awaiting completion') }}</span></div>
    </section>
    <div class="grid"><section class="card"><h2>{{ __('Operations flow') }}</h2><div style="display:grid;grid-template-columns:repeat(5,1fr);gap:10px;text-align:center"><div class="tag">{{ __('Lead') }}</div><div class="tag">{{ __('Evaluation') }}</div><div class="tag">{{ __('Listing') }}</div><div class="tag">{{ __('Viewing') }}</div><div class="tag">{{ __('Sale / Lease') }}</div></div></section>
    <aside class="card"><h2>{{ __('Quick actions') }}</h2><nav class="nav"><a href="{{ route('leads.index') }}">{{ __('Add a lead') }}</a><a href="{{ route('properties.index') }}">{{ __('Add a property') }}</a><a href="{{ route('valuations.index') }}">{{ __('Request an evaluation') }}</a><a href="{{ route('mail.index') }}">{{ __('Open Mail Centre') }}</a></nav></aside></div>
</x-app-layout>
