<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Inloggen en uitloggen voor de beheeromgeving. Zelf gebouwd, zonder
 * starterkit: er is maar één beheerder en geen registratie.
 */
class LoginController extends Controller
{
    /** GET /admin/login — het formulier. */
    public function create(): View
    {
        return view('admin.login');
    }

    /** POST /admin/login */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Nieuw sessie-id én CSRF-token na het inloggen, zodat een sessie-id
        // dat iemand vooraf heeft weten te bemachtigen niets meer waard is.
        // Laravel doet het id ook al bij Auth::attempt; dit maakt het expliciet.
        $request->session()->regenerate();

        // Terug naar de pagina die je wilde openen, anders naar de start.
        return redirect()->intended(route('admin.dashboard'));
    }

    /** POST /admin/logout */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Je bent uitgelogd.');
    }
}
