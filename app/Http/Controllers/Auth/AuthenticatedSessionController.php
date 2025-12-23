<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\utenti;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'userid' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Verifica credenziali tramite API nel model utenti
        $result = utenti::verifica($request->userid, $request->password);

        if (($result['header']['login'] ?? 'KO') !== 'OK') {
            throw ValidationException::withMessages([
                'userid' => $result['header']['error'] ?? __('auth.failed'),
            ]);
        }

        // Recupera l'utente locale corrispondente
        $user = utenti::where('userid', $request->userid)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'userid' => 'Utente non trovato nel database locale.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::home());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
