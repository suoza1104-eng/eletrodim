<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — EletroDIM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'brand-yellow': '#F2C300',
                        'brand-yellow-hover': '#D9AE00',
                        'brand-black': '#111111',
                        'brand-dark': '#1E1E1E',
                        'brand-gray': '#555555',
                        'brand-light': '#EDEDED',
                        'brand-bg': '#F7F7F7',
                    }
                }
            }
        }
    </script>
    @livewireStyles
    @stack('styles')
</head>
<body class="bg-brand-bg min-h-screen flex">

    <!-- Sidebar Admin -->
    <aside class="w-64 bg-brand-dark min-h-screen flex flex-col fixed left-0 top-0 z-30">
        <!-- Logo -->
        <div class="p-6 border-b border-gray-700">
            <h1 class="text-2xl font-black text-brand-yellow">EletroDIM</h1>
            <p class="text-orange-400 text-xs font-semibold mt-0.5 uppercase tracking-widest">Administrador</p>
        </div>

        <!-- Nav Admin -->
        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.dashboard') ? 'bg-brand-yellow text-brand-black' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Painel Admin
            </a>
            <a href="{{ route('admin.users') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.users*') ? 'bg-brand-yellow text-brand-black' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Alunos
            </a>
            <a href="{{ route('admin.logs.login') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.logs.login*') ? 'bg-brand-yellow text-brand-black' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Logs de Acesso
            </a>
            <a href="{{ route('admin.logs.webhooks') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('admin.logs.webhooks*') ? 'bg-brand-yellow text-brand-black' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Logs de Webhook
            </a>
            <a href="{{ route('student.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors text-gray-300 hover:bg-gray-700 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Visão do Aluno
            </a>
        </nav>

        <!-- User info + logout -->
        <div class="p-4 border-t border-gray-700">
            <p class="text-gray-400 text-xs mb-1">Logado como</p>
            <p class="text-white text-sm font-medium truncate">{{ Auth::user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-left text-gray-400 hover:text-white text-sm flex items-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sair
                </button>
            </form>
        </div>
    </aside>

    <!-- Main content -->
    <div class="ml-64 flex-1 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">@yield('page-title', 'Painel Admin')</h2>
                <p class="text-sm text-gray-500">@yield('page-subtitle', '')</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">Admin</span>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>
</html>
