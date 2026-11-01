<x-app-layout title="Mail Centre">
    <div class="heading"><div><h1>Mail Centre</h1><p>One secure inbox across Meyar, Marsous, and Al Ramz.</p></div></div>
    <div style="display:grid;grid-template-columns:230px 1fr;gap:18px">
        <aside class="card"><button class="btn" disabled>Compose</button><h2 style="margin-top:22px">Mailboxes</h2>@forelse($mailboxes as $mailbox)<div style="padding:10px 0;border-bottom:1px solid var(--line)"><strong>{{ $mailbox->display_name }}</strong><br><small>{{ $mailbox->email }}</small></div>@empty<div class="empty" style="padding:22px 4px">Mailboxes will appear after secure Plesk configuration.</div>@endforelse</aside>
        <section class="card"><h2>Unified inbox</h2>@if($messages->isEmpty())<div class="empty">No synchronized messages yet.</div>@else<table><thead><tr><th>From</th><th>Subject</th><th>Mailbox</th><th>Received</th></tr></thead><tbody>@foreach($messages as $message)<tr><td><strong>{{ $message->from_name ?: $message->from_address }}</strong><br><small>{{ $message->from_address }}</small></td><td>{{ $message->subject ?: '(No subject)' }}<br><small>{{ $message->preview }}</small></td><td>{{ $message->mailbox->email }}</td><td>{{ $message->received_at?->diffForHumans() }}</td></tr>@endforeach</tbody></table>{{ $messages->links() }}@endif</section>
    </div>
</x-app-layout>
