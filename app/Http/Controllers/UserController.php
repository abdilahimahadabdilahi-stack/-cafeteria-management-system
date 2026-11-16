<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->latest()->paginate(15);

        return view('staff.index', compact('users'));
    }

    public function create(): View
    {
        return view('staff.form', ['staffMember' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        User::query()->create($this->validatedAttributes($request));

        return redirect()->route('staff.index')->with('success', 'Staff account created.');
    }

    public function edit(User $staffMember): View
    {
        return view('staff.form', compact('staffMember'));
    }

    public function update(Request $request, User $staffMember): RedirectResponse
    {
        $isSelf = $staffMember->is(Auth::user());
        $removesAdminAccess = $request->input('role') !== 'admin' || ! $request->boolean('status');

        if ($isSelf && $removesAdminAccess) {
            return back()->with('error', 'You cannot remove or deactivate your own administrator account.');
        }

        if (
            $staffMember->role === 'admin'
            && $staffMember->status
            && $removesAdminAccess
            && User::query()->where('role', 'admin')->where('status', true)->count() === 1
        ) {
            return back()->with('error', 'At least one active administrator account must remain.');
        }

        $staffMember->update($this->validatedAttributes($request, $staffMember));

        return redirect()->route('staff.index')->with('success', 'Staff account updated.');
    }

    public function destroy(User $staffMember): RedirectResponse
    {
        if ($staffMember->is(Auth::user())) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if (
            $staffMember->role === 'admin'
            && $staffMember->status
            && User::query()->where('role', 'admin')->where('status', true)->count() === 1
        ) {
            return back()->with('error', 'At least one active administrator account must remain.');
        }

        if ($staffMember->orders()->exists() || $staffMember->expenses()->exists() || $staffMember->shifts()->exists()) {
            return back()->with('error', 'This staff account has transaction history. Deactivate it instead of deleting it.');
        }

        $staffMember->delete();

        return redirect()->route('staff.index')->with('success', 'Staff account deleted.');
    }

    private function validatedAttributes(Request $request, ?User $staffMember = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($staffMember?->id),
            ],
            'role' => ['required', Rule::in(['admin', 'cashier', 'kitchen'])],
            'phone' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'boolean'],
            'password' => [$staffMember ? 'nullable' : 'required', 'string', 'min:8'],
        ];

        $validated = $request->validate($rules);

        if ($staffMember && blank($validated['password'])) {
            unset($validated['password']);
        }

        return $validated;
    }
}
