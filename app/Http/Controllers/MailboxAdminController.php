<?php

namespace App\Http\Controllers;

use App\Models\Mailbox;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MailboxAdminController extends Controller
{
    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->wherePivot('role', 'admin')->get();
        abort_if($companies->isEmpty(), 403);
        $mailboxes = Mailbox::query()->whereIn('company_id', $companies->modelKeys())->with(['company', 'users'])->get();
        $users = User::query()->whereHas('companies', fn ($query) => $query->whereIn('companies.id', $companies->modelKeys()))->orderBy('name')->get();

        return view('mail.settings', compact('companies', 'mailboxes', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->wherePivot('role', 'admin')->pluck('companies.id')->all();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($companyIds)], 'user_id' => ['required', 'integer'],
            'email' => ['required', 'email', 'max:255'], 'display_name' => ['required', 'string', 'max:255'],
            'inbound_host' => ['required', 'string', 'max:255'], 'inbound_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'outbound_host' => ['required', 'string', 'max:255'], 'outbound_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'encryption' => ['required', Rule::in(['tls', 'ssl'])], 'username' => ['required', 'string', 'max:255'], 'password' => ['required', 'string'],
        ]);
        $user = User::query()->whereHas('companies', fn ($query) => $query->where('companies.id', $data['company_id']))->findOrFail($data['user_id']);
        $mailbox = Mailbox::query()->updateOrCreate(['email' => $data['email']], [
            'company_id' => $data['company_id'], 'display_name' => $data['display_name'],
            'inbound_host' => $data['inbound_host'], 'inbound_port' => $data['inbound_port'],
            'outbound_host' => $data['outbound_host'], 'outbound_port' => $data['outbound_port'],
            'encryption' => $data['encryption'], 'encrypted_credentials' => ['username' => $data['username'], 'password' => $data['password']], 'is_active' => true,
        ]);
        $mailbox->users()->syncWithoutDetaching([$user->id => ['can_send' => true]]);

        return redirect()->route('mail.settings')->with('success', 'Mailbox encrypted and assigned.');
    }
}
