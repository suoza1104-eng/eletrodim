<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Página não encontrada | EletroDIM</title>
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
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-black min-h-screen flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <!-- Código de erro -->
        <p class="text-brand-yellow text-8xl font-black mb-4 leading-none">404</p>

        <!-- Ícone -->
        <div class="w-16 h-16 bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-3">Página não encontrada</h1>
        <p class="text-gray-400 text-sm mb-8 leading-relaxed">
            A página que você está procurando não existe ou foi movida.<br>
            Verifique o endereço ou volte ao início.
        </p>

        <a href="{{ url('/') }}"
           class="inline-flex items-center gap-2 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black
                  font-bold px-6 py-3 rounded-xl text-sm transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar ao início
        </a>
    </div>
</body>
</html>
