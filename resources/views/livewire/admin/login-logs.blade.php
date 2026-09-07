<div>
    <!-- Barra de filtros -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Buscar por e-mail..."
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent">
        </div>
        <select wire:model.live="statusFilter"
                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow">
            <option value="">Todos os status</option>
            <option value="success">Sucesso</option>
            <option value="failed">Falha</option>
        </select>
    </div>

    <!-- Tabela de logs -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors duration-200">
        @if($logs->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400">Nenhum registro de login encontrado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">E-mail</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usuário</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Data / Hora</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mensagem</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                        @foreach($logs as $log)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-3 text-sm text-gray-800 dark:text-gray-200 font-medium">
                                {{ $log->email }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $log->user?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 font-mono">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="px-6 py-3">
                                @if($log->status === 'success')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                        Sucesso
                                    </span>
                                @elseif($log->status === 'failed')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                        Falha
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                        {{ $log->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $log->message ?? '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
