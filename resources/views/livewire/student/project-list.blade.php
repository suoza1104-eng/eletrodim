<div>
    <!-- Barra de ações -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Buscar por nome ou cliente..."
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent">
        </div>
        <select wire:model.live="statusFilter"
                class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow">
            <option value="">Todos os status</option>
            <option value="draft">Rascunho</option>
            <option value="in_progress">Em andamento</option>
            <option value="completed">Concluído</option>
        </select>
        <a href="{{ route('student.project.new') }}"
           class="bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Projeto
        </a>
    </div>

    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabela de projetos -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($projects->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-500 mb-4">Nenhum projeto encontrado.</p>
                <a href="{{ route('student.project.new') }}"
                   class="bg-brand-yellow text-brand-black font-bold px-5 py-2.5 rounded-xl text-sm">
                    Criar Projeto
                </a>
            </div>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Projeto / Cliente</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Progresso</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Criado em</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($projects as $project)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900 text-sm">{{ $project->name }}</p>
                            <p class="text-gray-400 text-xs mt-0.5">{{ $project->client_name ?? 'Sem cliente' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $colors = [
                                    'draft'       => 'bg-gray-100 text-gray-600',
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    'completed'   => 'bg-green-100 text-green-700',
                                ];
                                $labels = [
                                    'draft'       => 'Rascunho',
                                    'in_progress' => 'Em andamento',
                                    'completed'   => 'Concluído',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $colors[$project->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $labels[$project->status] ?? $project->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-brand-yellow h-1.5 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500">{{ $project->progress_percent }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $project->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('student.project.edit', $project->id) }}"
                                   class="text-xs font-medium text-blue-600 hover:text-blue-800 px-2.5 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                                    Editar
                                </a>
                                <button wire:click="duplicateProject({{ $project->id }})"
                                        class="text-xs font-medium text-gray-600 hover:text-gray-800 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                    Duplicar
                                </button>
                                @if($project->status === 'completed')
                                <a href="{{ route('report.pdf', $project->id) }}"
                                   class="text-xs font-medium text-green-600 hover:text-green-800 px-2.5 py-1.5 rounded-lg hover:bg-green-50 transition-colors">
                                    PDF
                                </a>
                                @endif
                                <button wire:click="confirmDelete({{ $project->id }})"
                                        class="text-xs font-medium text-red-500 hover:text-red-700 px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                    Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de confirmação de exclusão -->
    @if($confirmingDelete)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm w-full">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Excluir projeto?</h3>
            <p class="text-gray-500 text-sm mb-6">
                Esta ação não pode ser desfeita. Todos os dados do projeto serão removidos permanentemente.
            </p>
            <div class="flex gap-3">
                <button wire:click="cancelDelete"
                        class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button wire:click="deleteProject"
                        class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors">
                    Excluir
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
