<div>
    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Abas -->
    <div class="flex gap-1 mb-6 bg-gray-100 p-1 rounded-xl w-fit">
        <button wire:click="switchTab('out')"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors
                       {{ $tab === 'out' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Saída
        </button>
        <button wire:click="switchTab('in')"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-colors
                       {{ $tab === 'in' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
            Entrada
        </button>
    </div>

    <!-- Aba: Webhooks de Saída -->
    @if($tab === 'out')
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($outEvents->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-500">Nenhum webhook de saída registrado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Evento</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuário</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tentativas</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Último erro</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Enviado em</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($outEvents as $event)
                        @php
                            $outStatusColors = [
                                'pending' => 'bg-orange-100 text-orange-700',
                                'sent'    => 'bg-green-100 text-green-700',
                                'failed'  => 'bg-red-100 text-red-700',
                            ];
                            $outStatusLabels = [
                                'pending' => 'Pendente',
                                'sent'    => 'Enviado',
                                'failed'  => 'Falhou',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3">
                                <span class="text-sm font-mono text-gray-800">{{ $event->event_type }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ $event->user?->name ?? '—' }}
                                @if($event->user)
                                    <p class="text-xs text-gray-400">{{ $event->user->email }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $outStatusColors[$event->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $outStatusLabels[$event->status] ?? $event->status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-600">
                                {{ $event->attempts ?? 0 }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 max-w-xs">
                                @if($event->last_error)
                                    <span class="block truncate text-red-600 text-xs" title="{{ $event->last_error }}">
                                        {{ $event->last_error }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $event->sent_at ? $event->sent_at->format('d/m/Y H:i:s') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <button wire:click="requeue({{ $event->id }})"
                                        class="text-xs font-medium text-orange-600 hover:text-orange-800 px-2.5 py-1.5 rounded-lg hover:bg-orange-50 transition-colors whitespace-nowrap">
                                    Reenviar
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $outEvents->links() }}
            </div>
        @endif
    </div>
    @endif

    <!-- Aba: Webhooks de Entrada -->
    @if($tab === 'in')
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($inEvents->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-500">Nenhum webhook de entrada registrado.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Event ID</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Recebido em</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Processado em</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($inEvents as $event)
                        @php
                            $inStatusColors = [
                                'pending'   => 'bg-orange-100 text-orange-700',
                                'processed' => 'bg-green-100 text-green-700',
                                'failed'    => 'bg-red-100 text-red-700',
                                'ignored'   => 'bg-gray-100 text-gray-500',
                            ];
                            $inStatusLabels = [
                                'pending'   => 'Pendente',
                                'processed' => 'Processado',
                                'failed'    => 'Falhou',
                                'ignored'   => 'Ignorado',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-sm font-mono text-gray-700">
                                <span class="truncate max-w-[180px] block" title="{{ $event->event_id }}">
                                    {{ $event->event_id }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm font-mono text-gray-800">
                                {{ $event->event_type }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $inStatusColors[$event->process_status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $inStatusLabels[$event->process_status] ?? $event->process_status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $event->received_at ? $event->received_at->format('d/m/Y H:i:s') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-sm text-gray-500 whitespace-nowrap">
                                {{ $event->processed_at ? $event->processed_at->format('d/m/Y H:i:s') : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $inEvents->links() }}
            </div>
        @endif
    </div>
    @endif
</div>
