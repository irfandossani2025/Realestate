<?php

namespace App\Http\Controllers;

use App\Models\MailMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MailCentreController extends Controller
{
    public function index(Request $request): View
    {
        $mailboxes = $request->user()->mailboxes()->where('mailboxes.is_active', true)->with('company')->get();
        $messages = MailMessage::query()->whereIn('mailbox_id', $mailboxes->modelKeys())->with('mailbox')->latest('received_at')->paginate(25);

        return view('mail.index', compact('mailboxes', 'messages'));
    }
}
