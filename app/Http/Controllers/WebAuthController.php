<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Handle the login form submission using Laravel's session-based
     * 'web' guard — this is separate from the API's Sanctum token login.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'username' => 'Those credentials do not match our records.',
            ])->onlyInput('username');
        }

        $request->session()->regenerate();
        Auth::user()->update(['last_login' => now()]);

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
