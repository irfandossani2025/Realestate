<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    private const ROLES = ['admin', 'manager', 'marketing_manager', 'evaluator', 'sales_agent', 'leasing_agent', 'finance', 'property_manager', 'maintenance', 'team_member'];

    public function index(Request $request): View
    {
        $companies = $this->managedCompanies($request);
        abort_if($companies->isEmpty(), 403);
        $users = User::query()
            ->whereHas('companies', fn ($query) => $query->whereIn('companies.id', $companies->modelKeys()))
            ->with(['companies' => fn ($query) => $query->whereIn('companies.id', $companies->modelKeys())])
            ->orderBy('name')
            ->paginate(20);

        return view('staff.index', ['companies' => $companies, 'roles' => self::ROLES, 'users' => $users]);
    }

    public function store(Request $request): RedirectResponse
    {
        $companies = $this->managedCompanies($request);
        abort_if($companies->isEmpty(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['nullable', 'string', 'min:12'],
            'company_id' => ['required', Rule::in($companies->modelKeys())],
            'role' => ['required', Rule::in(self::ROLES)],
        ]);
        $user = User::query()->where('email', $data['email'])->first();
        if (! $user && empty($data['password'])) {
            throw ValidationException::withMessages(['password' => 'A password is required for a new employee.']);
        }
        if (! $user) {
            $user = User::query()->create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password'])]);
        } else {
            $user->update(array_filter(['name' => $data['name'], 'password' => empty($data['password']) ? null : Hash::make($data['password'])]));
        }
        $user->companies()->syncWithoutDetaching([$data['company_id'] => ['role' => $data['role'], 'is_active' => true]]);

        return redirect()->route('staff.index')->with('success', 'Employee access saved.');
    }

    private function managedCompanies(Request $request)
    {
        return $request->user()->companies()->wherePivot('is_active', true)->wherePivotIn('role', ['admin', 'manager'])->orderBy('name')->get();
    }
}
