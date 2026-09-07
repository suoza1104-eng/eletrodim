<div>
    <!-- Flash messages -->
    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Barra de ações -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Buscar por nome ou e-mail..."
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent">
        </div>
        <select wire:model.live="statusFilter"
                class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow">
            <option value="">Todos os status</option>
            <option value="active">Ativo</option>
            <option value="blocked">Bloqueado</option>
            <option value="cancelled">Cancelado</option>
            <option value="expired">Expirado</option>
        </select>
        <button wire:click="openCreateForm"
                class="bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold px-5 py-2.5 rounded-xl text-sm transition-colors flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Aluno
        </button>
    </div>

    <!-- Tabela de alunos -->
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        @if($users->isEmpty())
            <div class="p-12 text-center">
                <p class="text-gray-500 mb-4">Nenhum aluno encontrado.</p>
                <button wire:click="openCreateForm"
                        class="bg-brand-yellow text-brand-black font-bold px-5 py-2.5 rounded-xl text-sm">
                    Cadastrar Aluno
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nome / E-mail</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Telefone</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Projetos</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acesso até</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Último login</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($users as $user)
                        @php
                            $statusColors = [
                                'active'    => 'bg-green-100 text-green-700',
                                'blocked'   => 'bg-red-100 text-red-700',
                                'cancelled' => 'bg-gray-100 text-gray-600',
                                'expired'   => 'bg-orange-100 text-orange-700',
                            ];
                            $statusLabels = [
                                'active'    => 'Ativo',
                                'blocked'   => 'Bloqueado',
                                'cancelled' => 'Cancelado',
                                'expired'   => 'Expirado',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900 text-sm">{{ $user->name }}</p>
                                <p class="text-gray-400 text-xs mt-0.5">{{ $user->email }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->phone ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusColors[$user->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusLabels[$user->status] ?? $user->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->projects_count }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->access_expires_at ? $user->access_expires_at->format('d/m/Y') : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEditForm({{ $user->id }})"
                                            class="text-xs font-medium text-blue-600 hover:text-blue-800 px-2.5 py-1.5 rounded-lg hover:bg-blue-50 transition-colors">
                                        Editar
                                    </button>
                                    @if($user->status !== 'active')
                                    <button wire:click="quickAction({{ $user->id }}, 'activate')"
                                            class="text-xs font-medium text-green-600 hover:text-green-800 px-2.5 py-1.5 rounded-lg hover:bg-green-50 transition-colors">
                                        Ativar
                                    </button>
                                    @endif
                                    @if($user->status !== 'blocked')
                                    <button wire:click="quickAction({{ $user->id }}, 'block')"
                                            class="text-xs font-medium text-red-500 hover:text-red-700 px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition-colors">
                                        Bloquear
                                    </button>
                                    @endif
                                    @if($user->status !== 'cancelled')
                                    <button wire:click="quickAction({{ $user->id }}, 'cancel')"
                                            class="text-xs font-medium text-gray-500 hover:text-gray-700 px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                        Cancelar
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Slide-over: Formulário criar/editar aluno -->
    @if($showForm)
    <div class="fixed inset-0 z-50 overflow-hidden" aria-modal="true">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-40" wire:click="$set('showForm', false)"></div>

        <!-- Painel lateral -->
        <div class="absolute inset-y-0 right-0 flex flex-col w-full max-w-md bg-white shadow-2xl">
            <!-- Cabeçalho -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
                <h2 class="text-lg font-bold text-gray-900">
                    {{ $editingUserId ? 'Editar Aluno' : 'Novo Aluno' }}
                </h2>
                <button wire:click="$set('showForm', false)"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Corpo -->
            <div class="flex-1 overflow-y-auto px-6 py-6">
                <form wire:submit.prevent="saveUser" class="space-y-5">

                    <!-- Nome -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Nome <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="name" type="text" placeholder="Nome completo"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent @error('name') border-red-400 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- E-mail -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            E-mail <span class="text-red-500">*</span>
                        </label>
                        <input wire:model="email" type="email" placeholder="aluno@email.com"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Telefone</label>
                        <input wire:model="phone" type="text" placeholder="(00) 00000-0000"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent @error('phone') border-red-400 @enderror">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Senha -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Senha {{ $editingUserId ? '(deixe em branco para manter)' : '' }}
                            @if(!$editingUserId)<span class="text-red-500">*</span>@endif
                        </label>
                        <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent @error('password') border-red-400 @enderror">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="status"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('status') border-red-400 @enderror">
                            <option value="active">Ativo</option>
                            <option value="blocked">Bloqueado</option>
                            <option value="cancelled">Cancelado</option>
                            <option value="expired">Expirado</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acesso até -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Acesso até</label>
                        <input wire:model="accessExpiresAt" type="date"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent @error('accessExpiresAt') border-red-400 @enderror">
                        @error('accessExpiresAt')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-400">Deixe em branco para acesso ilimitado.</p>
                    </div>

                    <!-- Botões -->
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="$set('showForm', false)"
                                class="flex-1 px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black rounded-xl text-sm font-bold transition-colors">
                            {{ $editingUserId ? 'Salvar Alterações' : 'Criar Aluno' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
