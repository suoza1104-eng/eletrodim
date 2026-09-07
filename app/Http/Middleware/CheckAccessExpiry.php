<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

// Bloqueia usuários com access_expires_at vencido
class CheckAccessExpiry
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (
                $user->access_expires_at !== null &&
                now()->gt($user->access_expires_at)
            ) {
                $expiresAt = $user->access_expires_at->format('d/m/Y');

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => "Seu acesso expirou em {$expiresAt}.",
                ]);
            }
        }

        return $next($request);
    }
}
