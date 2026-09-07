<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EletroDIM')</title>
    <script>
        // Aplica o tema salvo antes da renderização, evitando flash de tela clara/escura errada.
        (function () {
            const saved = localStorage.getItem('eletrodim-theme');
            const isDark = saved ? saved === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (isDark) document.documentElement.classList.add('dark');
        })();
    </script>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
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
</head>
<body
    class="bg-brand-black dark:bg-gray-950 min-h-screen flex items-center justify-center p-4 transition-colors duration-200"
    x-data="{
        darkMode: document.documentElement.classList.contains('dark'),
        toggleTheme() {
            this.darkMode = !this.darkMode;
            document.documentElement.classList.toggle('dark', this.darkMode);
            localStorage.setItem('eletrodim-theme', this.darkMode ? 'dark' : 'light');
        }
    }"
>
    <!-- Toggle de tema claro/escuro -->
    <button @click="toggleTheme()" class="fixed top-4 right-4 p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition" title="Alternar tema">
        <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
        <svg x-show="darkMode" style="display:none" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </button>

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-black text-brand-yellow tracking-tight">EletroDIM</h1>
            <p class="text-gray-400 text-sm mt-1">Dimensionar ficou simples</p>
        </div>
        @yield('content')
    </div>
    @livewireScripts
</body>
</html>
