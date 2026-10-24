<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    // Une suspension prend effet tout de suite, même si le joueur est déjà connecté.
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user !== null && $user->status !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => 'Votre compte est suspendu. Contactez l’accueil du club.']);
        }

        return $next($request);
    }
}
