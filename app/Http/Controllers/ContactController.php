<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    private const TYPES = ['client', 'owner', 'buyer', 'tenant', 'contractor', 'supplier'];

    public function index(Request $request): View
    {
        $companies = $request->user()->companies()->wherePivot('is_active', true)->get();
        $contacts = Contact::query()->whereIn('company_id', $companies->modelKeys())->with('company')->orderBy('name')->paginate(20);

        return view('contacts.index', ['companies' => $companies, 'contacts' => $contacts, 'types' => self::TYPES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companyIds = $request->user()->companies()->wherePivot('is_active', true)->pluck('companies.id')->all();
        $data = $request->validate([
            'company_id' => ['required', Rule::in($companyIds)], 'type' => ['required', Rule::in(self::TYPES)],
            'name' => ['required', 'string', 'max:255'], 'name_ar' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:32'],
            'nationality' => ['nullable', 'string', 'max:100'], 'preferred_language' => ['required', Rule::in(['en', 'ar'])],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        Contact::query()->create([...$data, 'is_active' => true]);

        return redirect()->route('contacts.index')->with('success', 'Contact saved.');
    }
}
