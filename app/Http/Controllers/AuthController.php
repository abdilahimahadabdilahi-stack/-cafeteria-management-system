<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForRole((string) Auth::user()->role);
        }

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['status' => true], $request->boolean('remember'))) {
            return back()
                ->withErrors(['username' => 'The provided credentials are invalid or the account is inactive.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return $this->redirectForRole((string) Auth::user()->role);
    }

    // --- SHAQADA 1: Soo bandhigidda Bogga Register-ka ---
    public function createRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectForRole((string) Auth::user()->role);
        }

        return view('auth.register');
    }

    // --- SHAQADA 2: Kaydinta xogta isdiiwaangelinta (oo wadata Role-ka) ---
    public function storeRegister(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:admin,cashier,kitchen'], // Xaqiijinta role-ka
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Kaydinta role-ka uu soo doortay
            'status' => true,
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return $this->redirectForRole((string) $user->role);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectForRole(string $role): RedirectResponse
    {
        return match ($role) {
            'admin' => redirect()->route('dashboard'),
            'cashier' => redirect()->route('pos.index'),
            'kitchen' => redirect()->route('dashboard'),
            default => abort(403),
        };
    }
}