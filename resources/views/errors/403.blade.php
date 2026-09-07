<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 — Acesso Negado | EletroDIM</title>
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
        <p class="text-brand-yellow text-8xl font-black mb-4 leading-none">403</p>

        <!-- Ícone -->
        <div class="w-16 h-16 bg-red-900/30 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-white mb-3">Acesso Negado</h1>
        <p class="text-gray-400 text-sm mb-8 leading-relaxed">
            Você não tem permissão para acessar esta página.<br>
            Se acredita que isso é um erro, entre em contato com o suporte.
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
