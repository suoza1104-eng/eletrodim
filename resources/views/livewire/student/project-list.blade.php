<div>
    <!-- Barra de ações -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Buscar por nome ou cliente..."
                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent">
        </div>
        <select wire:model.live="statusFilter"
                class="px-4 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow">
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
        <div class="mb-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabela de projetos -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden transition-colors duration-200">
        @if($projects->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-500 dark:text-gray-400 mb-4">Nenhum projeto encontrado.</p>
                <a href="{{ route('student.project.new') }}"
                   class="bg-brand-yellow text-brand-black font-bold px-5 py-2.5 rounded-xl text-sm">
                    Criar Projeto
                </a>
            </div>
        @else
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Projeto / Cliente</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progresso</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Criado em</th>
                        <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($projects as $project)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $project->name }}</p>
                            <p class="text-gray-400 dark:text-gray-500 text-xs mt-0.5">{{ $project->client_name ?? 'Sem cliente' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $colors = [
                                    'draft'       => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                    'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                    'completed'   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                ];
                                $labels = [
                                    'draft'       => 'Rascunho',
                                    'in_progress' => 'Em andamento',
                                    'completed'   => 'Concluído',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $colors[$project->status] ?? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                {{ $labels[$project->status] ?? $project->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="bg-brand-yellow h-1.5 rounded-full" style="width: {{ $project->progress_percent }}%"></div>
                                </div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $project->progress_percent }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $project->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('student.project.edit', $project->id) }}"
                                   class="text-xs font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 px-2.5 py-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/30 transition-colors">
                                    Editar
                                </a>
                                <button wire:click="duplicateProject({{ $project->id }})"
                                        class="text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-100 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                    Duplicar
                                </button>
                                @if($project->status === 'completed')
                                <a href="{{ route('report.pdf', $project->id) }}"
                                   class="text-xs font-medium text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-300 px-2.5 py-1.5 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/30 transition-colors">
                                    PDF
                                </a>
                                @endif
                                <button wire:click="confirmDelete({{ $project->id }})"
                                        class="text-xs font-medium text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 px-2.5 py-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/30 transition-colors">
                                    Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

    <!-- Modal de confirmação de exclusão -->
    @if($confirmingDelete)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 max-w-sm w-full transition-colors duration-200">
            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Excluir projeto?</h3>
            <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">
                Esta ação não pode ser desfeita. Todos os dados do projeto serão removidos permanentemente.
            </p>
            <div class="flex gap-3">
                <button wire:click="cancelDelete"
                        class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
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
