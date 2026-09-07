<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use App\Models\WebhookOutEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Processa o login do usuário.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Registra log de login com sucesso
            LoginLog::create([
                'user_id'    => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status'     => 'success',
            ]);

            $isFirstLogin = $user->first_login_at === null;

            // Atualiza timestamps de login
            $user->last_login_at = now();
            if ($isFirstLogin) {
                $user->first_login_at = now();
            }
            $user->save();

            // Dispara webhook de primeiro login (apenas para alunos)
            if ($isFirstLogin && $user->role === 'student') {
                WebhookOutEvent::create([
                    'event_type'   => 'student.first_login',
                    'user_id'      => $user->id,
                    'payload_json' => [
                        'user_id'  => $user->id,
                        'email'    => $user->email,
                        'name'     => $user->name,
                        'login_at' => now()->toIso8601String(),
                    ],
                    'status'  => 'pending',
                ]);
            }

            // Redireciona conforme papel do usuário
            if ($user->role === 'admin') {
                return redirect()->intended('/admin/dashboard');
            }

            return redirect()->intended('/dashboard');
        }

        // Registra log de falha (sem user_id pois o usuário pode não existir)
        LoginLog::create([
            'user_id'    => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status'     => 'failed',
            'email_attempted' => $request->input('email'),
        ]);

        return back()->withErrors([
            'email' => 'E-mail ou senha inválidos.',
        ])->onlyInput('email');
    }

    /**
     * Encerra a sessão do usuário.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
