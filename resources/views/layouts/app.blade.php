<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EletroDIM') — EletroDIM</title>
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
<body class="bg-brand-bg min-h-screen" x-data="{ sidebarOpen: false }">

    <!-- Mobile overlay -->
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-20 lg:hidden"
        style="display:none"
    ></div>

    <!-- Sidebar -->
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        class="w-64 bg-brand-dark min-h-screen flex flex-col fixed left-0 top-0 z-30 transition-transform duration-200 ease-in-out"
    >
        <!-- Logo -->
        <div class="p-6 border-b border-gray-700 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-black text-brand-yellow">EletroDIM</h1>
                <p class="text-gray-500 text-xs mt-0.5">Dimensionar ficou simples</p>
            </div>
            <!-- Close button (mobile only) -->
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 p-4 space-y-1">
            <a href="{{ route('student.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('student.dashboard') ? 'bg-brand-yellow text-brand-black' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('student.projects') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                      {{ request()->routeIs('student.projects*') ? 'bg-brand-yellow text-brand-black' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Meus Projetos
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
    <div class="lg:ml-64 flex flex-col min-h-screen">
        <!-- Top bar -->
        <header class="bg-white border-b border-gray-200 px-4 md:px-8 py-4 flex items-center justify-between sticky top-0 z-10">
            <div class="flex items-center gap-3">
                <!-- Hamburger (mobile only) -->
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition -ml-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h2 class="text-lg md:text-xl font-semibold text-gray-800 leading-tight">@yield('page-title', 'Dashboard')</h2>
                    <p class="text-xs md:text-sm text-gray-500">@yield('page-subtitle', '')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="bg-brand-yellow text-brand-black text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wide">Aluno</span>
            </div>
        </header>

        <!-- Content -->
        <main class="flex-1 p-4 md:p-8">
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
