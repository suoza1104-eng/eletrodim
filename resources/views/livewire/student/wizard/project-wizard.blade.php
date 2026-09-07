<div class="max-w-6xl mx-auto" x-data="{}">
<div class="max-w-6xl mx-auto" x-data="{
        toastShow: false,
        toastType: 'success',
        toastMessage: '',
        toastTimer: null,
        triggerToast(msg, type = 'success') {
            if (!msg) return;
            this.toastMessage = msg;
            this.toastType = type;
            this.toastShow = true;
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => { this.toastShow = false; }, 3500);
        }
    }"
    x-on:toast.window="triggerToast($event.detail.message || ($event.detail[0] ? $event.detail[0].message : ''), $event.detail.type || ($event.detail[0] ? $event.detail[0].type : 'success'))"
    x-on:room-added.window="
        triggerToast('Cômodo criado com sucesso!', 'success');
        $nextTick(() => {
            setTimeout(() => {
                window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
            }, 100);
        });
    ">

    {{-- BANDEIRINHA RETANGULAR DE NOTIFICAÇÃO (CANTO SUPERIOR DIREITO) --}}
    <div x-show="toastShow"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-x-8 scale-95"
         x-transition:enter-end="opacity-100 translate-x-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-x-0 scale-100"
         x-transition:leave-end="opacity-0 translate-x-8 scale-95"
         style="display: none;"
         class="fixed top-5 right-5 z-50 max-w-sm w-full">
        <div :class="toastType === 'success'
                ? 'bg-emerald-600 text-white border-l-4 border-emerald-800 shadow-2xl'
                : 'bg-rose-600 text-white border-l-4 border-rose-800 shadow-2xl'"
             class="rounded-lg px-4 py-3 flex items-center justify-between gap-3 text-sm font-semibold tracking-wide">
            <div class="flex items-center gap-2.5">
                <template x-if="toastType === 'success'">
                    <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="toastType !== 'success'">
                    <svg class="w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </template>
                <span x-text="toastMessage"></span>
            </div>
            <button @click="toastShow = false" type="button" class="text-white/80 hover:text-white transition focus:outline-none ml-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         PROGRESS BAR
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        @php
            $pbMax = $projectId
                ? min((\App\Models\Project::find($projectId)?->progress_step ?? 1) + 1, \App\Livewire\Student\Wizard\ProjectWizard::TOTAL_STEPS)
                : 1;
        @endphp
        <div class="flex items-center gap-0 w-full">
            @foreach(\App\Livewire\Student\Wizard\ProjectWizard::STEP_LABELS as $step => $label)
                @php
                    $pbPast      = $step < $currentStep;
                    $pbCurrent   = $step === $currentStep;
                    $pbClickable = $step <= $pbMax && !$pbCurrent;
                @endphp
                @if($step > 1)
                    <div class="flex-1 h-1 rounded-full {{ $pbPast || $pbCurrent ? 'bg-brand-yellow' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endif
                @if($pbCurrent)
                    <div class="w-8 h-8 rounded-full text-sm font-black bg-brand-yellow text-brand-black flex items-center justify-center ring-2 ring-yellow-300 ring-offset-1 flex-none relative z-10">{{ $step }}</div>
                @elseif($pbClickable)
                    <button wire:click="goToStep({{ $step }})" title="{{ $label }}"
                        class="w-7 h-7 rounded-full text-xs font-bold flex items-center justify-center flex-none transition
                            {{ $pbPast ? 'bg-brand-yellow text-brand-black hover:bg-brand-yellow-hover' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-300' }}">{{ $step }}</button>
                @else
                    <div class="w-6 h-6 rounded-full text-xs font-bold bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 flex items-center justify-center flex-none">{{ $step }}</div>
                @endif
            @endforeach
        </div>
        <div class="mt-3 flex items-center justify-between text-sm">
            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ \App\Livewire\Student\Wizard\ProjectWizard::STEP_LABELS[$currentStep] }}</span>
            <span class="text-gray-400 dark:text-gray-500">Step {{ $currentStep }} de {{ \App\Livewire\Student\Wizard\ProjectWizard::TOTAL_STEPS }}</span>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         MENSAGENS FLASH
    ══════════════════════════════════════════════════════════ --}}
    @if($successMessage)
        <div class="mb-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-lg flex items-center gap-2 text-sm">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $successMessage }}
        </div>
    @endif
    @if($errorMessage)
        <div class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-lg flex items-center gap-2 text-sm">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $errorMessage }}
        </div>
    @endif

    {{-- ── DESFAZER ─────────────────────────────────────────── --}}
    @if(!empty($history))
    <div class="mb-4 flex items-center justify-between gap-3 bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5">
        <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ count($history) }} ação(ões) disponível(is) para desfazer
        </div>
        <button wire:click="undo"
            wire:loading.attr="disabled" wire:target="undo"
            class="inline-flex items-center gap-1.5 text-xs font-bold text-gray-700 dark:text-gray-300 hover:text-brand-black bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:border-gray-500 dark:hover:border-gray-400 rounded-lg px-3 py-1.5 transition disabled:opacity-50">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6M3 10l6-6"/></svg>
            Desfazer
        </button>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         CARD DE CONTEÚDO
    ══════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

        {{-- ─── STEP 1 — Dados do Projeto ─── --}}
        @if($currentStep === 1)
        <div class="p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-6">Dados do Projeto</h3>
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Nome do Projeto <span class="text-red-500">*</span></label>
                <input type="text" wire:model="name" placeholder="Ex: Residência João da Silva"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition @error('name') border-red-400 bg-red-50 dark:bg-red-900/20 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Nome do Cliente</label>
                    <input type="text" wire:model="clientName" placeholder="Nome completo"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Telefone</label>
                    <input type="text" wire:model="clientPhone" placeholder="(00) 00000-0000"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition @error('clientPhone') border-red-400 bg-red-50 dark:bg-red-900/20 @enderror">
                    @error('clientPhone')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">E-mail do Cliente</label>
                    <input type="email" wire:model="clientEmail" placeholder="cliente@email.com"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition @error('clientEmail') border-red-400 bg-red-50 dark:bg-red-900/20 @enderror">
                    @error('clientEmail')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Endereço</label>
                    <input type="text" wire:model="address" placeholder="Rua, número, bairro"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Cidade</label>
                    <input type="text" wire:model="city" placeholder="Cidade"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Estado (UF)</label>
                    <input type="text" wire:model="state" placeholder="SP" maxlength="2"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 uppercase focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Número de Andares da Residência</label>
                    <select wire:model="floorsCount"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition">
                        <option value="1">1 Andar (Térreo / Único)</option>
                        <option value="2">2 Andares (Térreo + 1º Andar)</option>
                        <option value="3">3 Andares (Térreo + 2 Andares)</option>
                        <option value="4">4 Andares</option>
                        <option value="5">5 Andares</option>
                    </select>
                </div>
            </div>
            <div class="mt-5">
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Observações</label>
                <textarea wire:model="observations" rows="3" placeholder="Informações adicionais sobre o projeto..."
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow focus:border-transparent transition resize-none"></textarea>
            </div>
        </div>
        @endif

        {{-- ─── STEP 2 — Cadastro dos Cômodos e Cargas Mínimas ─── --}}
        @if($currentStep === 2)
        <div class="p-4 md:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Cadastro dos Cômodos e Cargas Mínimas</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Cadastre os ambientes da instalação. O EletroDIM calcula automaticamente a iluminação e as tomadas mínimas conforme a NBR 5410.</p>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button wire:click="toggleFloorPlanEditor" type="button"
                        class="inline-flex items-center gap-1.5 {{ $showFloorPlanEditor ? 'bg-brand-black text-white' : 'bg-white dark:bg-gray-800 text-brand-black dark:text-gray-200 border border-gray-300 dark:border-gray-600' }} text-sm font-bold px-4 py-2.5 rounded-lg transition whitespace-nowrap shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        {{ $showFloorPlanEditor ? 'Fechar planta baixa' : 'Desenhar planta baixa' }}
                    </button>
                    <button wire:click="addRoom"
                        class="inline-flex items-center gap-1.5 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black text-sm font-bold px-4 py-2.5 rounded-lg transition flex-shrink-0 whitespace-nowrap shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Adicionar Cômodo
                    </button>
                </div>
            </div>

            @if($showFloorPlanEditor)
            <div class="mb-6 rounded-xl overflow-hidden border border-gray-300 dark:border-gray-600" x-data x-init="
                if (!window.__eletrodimFloorPlanListenerAttached) {
                    window.__eletrodimFloorPlanListenerAttached = true;
                    window.addEventListener('message', (e) => {
                        if (e.origin !== window.location.origin) return;
                        if (e.data && e.data.type === 'eletrodim:floorplan-rooms') {
                            $wire.importRoomsFromFloorPlan(e.data.comodos);
                        }
                    });
                }
            ">
                <iframe src="{{ asset('floor-plan-editor.html') }}" wire:ignore
                    style="width:100%;height:640px;border:0;display:block;"
                    title="Editor de planta baixa"></iframe>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-4 mb-6">Desenhe as paredes, nomeie os cômodos (feche o contorno primeiro) e clique em <strong>"Enviar cômodos para o projeto"</strong> no painel direito do editor. O tipo de cada cômodo é adivinhado pelo nome — confira e ajuste antes de avançar.</p>
            @endif

            @error('rooms')<p class="text-sm text-red-600 dark:text-red-400 mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded px-3 py-2">{{ $message }}</p>@enderror

            @forelse($rooms as $i => $room)
            @php
                $effectiveLightingVa = ($room['use_manual_lighting'] ?? false) && ($room['lighting_va_manual'] ?? '') !== ''
                    ? (int)$room['lighting_va_manual']
                    : (int)($room['lighting_va_calculated'] ?? 0);

                $effectiveTugQty600 = ($room['use_manual_tug_qty'] ?? false)
                    ? (($room['tug_qty_600_manual'] ?? '') !== '' ? (int)$room['tug_qty_600_manual'] : (int)($room['tug_qty_600_calculated'] ?? 0))
                    : (int)($room['tug_qty_600_calculated'] ?? 0);

                $effectiveTugQty100 = ($room['use_manual_tug_qty'] ?? false)
                    ? (($room['tug_qty_100_manual'] ?? '') !== '' ? (int)$room['tug_qty_100_manual'] : (int)($room['tug_qty_100_calculated'] ?? 0))
                    : (int)($room['tug_qty_100_calculated'] ?? 0);

                $effectiveTugQty = $effectiveTugQty600 + $effectiveTugQty100;
                $effectiveTugVa  = ($effectiveTugQty600 * 600) + ($effectiveTugQty100 * 100);

                $totalVa = $effectiveLightingVa + $effectiveTugVa;
                $hasCalc = ($room['lighting_va_calculated'] ?? null) !== null || ($room['tug_qty_calculated'] ?? null) !== null;
            @endphp
            <div class="bg-slate-50/70 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-700 rounded-xl overflow-hidden mb-5 shadow-sm transition-all hover:border-gray-300 dark:hover:border-gray-600" wire:key="room2-{{ $i }}">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border-b border-gray-200/80 dark:border-gray-700">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-yellow text-brand-black text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300 truncate">
                            @if($room['room_type'] ?? '')
                                {{ $room['room_type'] }}@if($room['description'] ?? ''): <span class="font-normal text-gray-500 dark:text-gray-400">{{ $room['description'] }}</span>@endif
                            @else
                                Novo cômodo
                            @endif
                        </span>
                        @if($hasCalc && $totalVa > 0)
                            <span class="flex-shrink-0 text-xs font-semibold text-green-700 dark:text-green-300 bg-green-100 border border-green-200 dark:border-green-800 px-2 py-0.5 rounded-full">{{ number_format($totalVa, 0, ',', '.') }} VA</span>
                        @endif
                    </div>
                    @if(count($rooms) > 1)
                    <button wire:click="removeRoom({{ $i }})" wire:confirm="Remover este cômodo e todos os seus dados?"
                        class="flex-shrink-0 p-1.5 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded-lg transition ml-2" title="Remover cômodo">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    @endif
                </div>

                <div class="p-4">
                    {{-- Input fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Andar / Localização <span class="text-red-500">*</span></label>
                            <select wire:model="rooms.{{ $i }}.floor_number"
                                class="w-full border rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-yellow border-gray-300 dark:border-gray-600">
                                @for($f = 1; $f <= max(1, (int)$floorsCount); $f++)
                                    <option value="{{ $f }}">
                                        @if($floorsCount > 1)
                                            {{ $f }}º Andar {{ $f === 1 ? '(Térreo)' : '' }}
                                        @else
                                            1º Andar (Térreo / Único)
                                        @endif
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Tipo de Cômodo <span class="text-red-500">*</span></label>
                            <select wire:model="rooms.{{ $i }}.room_type"
                                wire:change="calculateRoomLoads({{ $i }})"
                                class="w-full border rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('rooms.'.$i.'.room_type') border-red-400 bg-red-50 dark:bg-red-900/20 @else border-gray-300 dark:border-gray-600 @enderror">
                                <option value="">Selecione...</option>
                                @foreach(\App\Livewire\Student\Wizard\ProjectWizard::ROOM_TYPES as $rt)
                                    <option value="{{ $rt }}">{{ $rt }}</option>
                                @endforeach
                            </select>
                            @error('rooms.'.$i.'.room_type')<p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Descrição <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="rooms.{{ $i }}.description"
                                placeholder="Ex: Sala de estar"
                                class="w-full border rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('rooms.'.$i.'.description') border-red-400 bg-red-50 dark:bg-red-900/20 @else border-gray-300 dark:border-gray-600 @enderror">
                            @error('rooms.'.$i.'.description')<p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Área (m²) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model="rooms.{{ $i }}.area_m2"
                                wire:change="calculateRoomLoads({{ $i }})"
                                @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                @blur="evaluateCalcFormula($event.target)"
                                placeholder="m² (ex: =10*5)"
                                class="w-full border rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 text-center focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('rooms.'.$i.'.area_m2') border-red-400 bg-red-50 dark:bg-red-900/20 @else border-gray-300 dark:border-gray-600 @enderror">
                            @error('rooms.'.$i.'.area_m2')<p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Perímetro (m) <span class="text-red-500">*</span></label>
                            <input type="text" inputmode="decimal" wire:model="rooms.{{ $i }}.perimeter_m"
                                wire:change="calculateRoomLoads({{ $i }})"
                                @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                @blur="evaluateCalcFormula($event.target)"
                                placeholder="m (ex: =2*(10+5))"
                                class="w-full border rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 text-center focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('rooms.'.$i.'.perimeter_m') border-red-400 bg-red-50 dark:bg-red-900/20 @else border-gray-300 dark:border-gray-600 @enderror">
                            @error('rooms.'.$i.'.perimeter_m')<p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- Resultados dos cálculos --}}
                    @if($hasCalc)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">

                        {{-- Card: Iluminação --}}
                        <div class="bg-blue-50/80 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-3.5 h-full flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-1.5 mb-2 relative" x-data="{ showInfo: false }">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                        <span class="text-xs font-bold text-blue-800 dark:text-blue-300 uppercase tracking-wide">Iluminação</span>
                                    </div>
                                    <button @click="showInfo = !showInfo" @click.away="showInfo = false" type="button" class="w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-xs font-bold flex items-center justify-center hover:bg-blue-200 dark:hover:bg-blue-800 transition focus:outline-none" title="Informações da Norma (NBR 5410)">
                                        i
                                    </button>
                                    <div x-show="showInfo" x-transition.opacity style="display:none;" class="absolute right-0 top-7 z-30 w-64 p-3 bg-white dark:bg-gray-800 text-xs text-gray-700 dark:text-gray-200 rounded-lg shadow-xl border border-blue-200 dark:border-blue-800 leading-relaxed">
                                        <div class="font-bold text-blue-700 dark:text-blue-300 mb-1">NBR 5410 — Iluminação</div>
                                        {{ $room['lighting_rule_description'] ?: '100 VA até 6 m² + 60 VA a cada 4 m² inteiros adicionais.' }}
                                    </div>
                                </div>
                                <div class="text-2xl font-black text-blue-900 dark:text-blue-200">
                                    {{ number_format($effectiveLightingVa, 0, ',', '.') }} VA
                                </div>
                                @if($room['use_manual_lighting'] ?? false)
                                    <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 bg-amber-100/80 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-700 rounded px-1.5 py-0.5 mt-1 inline-block">Ajustado manualmente</p>
                                    <p class="text-xs text-blue-500 mt-0.5 line-through opacity-70">Cálculo: {{ $room['lighting_va_calculated'] ?? '—' }} VA</p>
                                @endif
                            </div>

                            {{-- Divider & Override --}}
                            <div class="mt-auto pt-3 border-t border-blue-200/70 dark:border-blue-800/70">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="rooms.{{ $i }}.use_manual_lighting"
                                        wire:change="calculateRoomLoads({{ $i }})"
                                        class="w-3.5 h-3.5 accent-blue-600 rounded">
                                    <span class="text-xs text-blue-700 dark:text-blue-300 font-medium">Ajustar manualmente</span>
                                </label>
                                @if($room['use_manual_lighting'] ?? false)
                                <div class="mt-2 flex items-center gap-1.5">
                                    <input type="text" inputmode="decimal" wire:model="rooms.{{ $i }}.lighting_va_manual"
                                        wire:change="calculateRoomLoads({{ $i }})"
                                        @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                        @blur="evaluateCalcFormula($event.target)"
                                        placeholder="VA (ex: =30*5)"
                                        class="flex-1 border border-blue-300 bg-white dark:bg-gray-800 rounded-lg px-2.5 py-1.5 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    <span class="text-xs text-blue-600 dark:text-blue-400 font-bold">VA</span>
                                </div>
                                <button wire:click="resetRoomManual({{ $i }}, 'use_manual_lighting')"
                                    class="mt-1.5 text-[11px] text-blue-600 hover:text-blue-800 dark:text-blue-300 underline font-medium">
                                    ↺ Restaurar automático
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- Card: Tomadas TUG --}}
                        <div class="bg-green-50/80 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-3.5 h-full flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-1.5 mb-2 relative" x-data="{ showInfo: false }">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        <span class="text-xs font-bold text-green-800 dark:text-green-300 uppercase tracking-wide">Tomadas TUG</span>
                                    </div>
                                    <button @click="showInfo = !showInfo" @click.away="showInfo = false" type="button" class="w-5 h-5 rounded-full bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-300 text-xs font-bold flex items-center justify-center hover:bg-green-200 dark:hover:bg-green-800 transition focus:outline-none" title="Informações da Norma (NBR 5410)">
                                        i
                                    </button>
                                    <div x-show="showInfo" x-transition.opacity style="display:none;" class="absolute right-0 top-7 z-30 w-64 p-3 bg-white dark:bg-gray-800 text-xs text-gray-700 dark:text-gray-200 rounded-lg shadow-xl border border-green-200 dark:border-green-800 leading-relaxed">
                                        <div class="font-bold text-green-700 dark:text-green-300 mb-1">NBR 5410 — Tomadas TUG</div>
                                        {{ $room['tug_rule_description'] ?: 'Área ≤ 6 m²: 1 tomada. Acima: 1 tomada a cada 5 m de perímetro (ou fração).' }}
                                    </div>
                                </div>
                                <div class="text-2xl font-black text-green-900 dark:text-green-200">
                                    {{ number_format($effectiveTugVa, 0, ',', '.') }} VA
                                </div>
                                <div class="text-xs font-semibold text-green-700 dark:text-green-300 mt-0.5">
                                    {{ $effectiveTugQty }} tomada{{ $effectiveTugQty !== 1 ? 's' : '' }}
                                    @if($effectiveTugQty600 > 0 || $effectiveTugQty100 > 0)
                                        <span class="text-[11px] font-normal text-green-600 dark:text-green-400 opacity-90">({{ $effectiveTugQty100 }}× 100VA + {{ $effectiveTugQty600 }}× 600VA)</span>
                                    @endif
                                </div>
                                @if($room['use_manual_tug_qty'] ?? false)
                                    <p class="text-[11px] font-semibold text-amber-700 dark:text-amber-300 bg-amber-100/80 dark:bg-amber-900/40 border border-amber-200 dark:border-amber-700 rounded px-1.5 py-0.5 mt-1 inline-block">Ajustado manualmente</p>
                                @endif
                            </div>

                            {{-- Divider & Manual controls --}}
                            <div class="mt-auto pt-3 border-t border-green-200/70 dark:border-green-800/70">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="rooms.{{ $i }}.use_manual_tug_qty"
                                        wire:change="calculateRoomLoads({{ $i }})"
                                        class="w-3.5 h-3.5 accent-green-600 rounded">
                                    <span class="text-xs text-green-700 dark:text-green-300 font-medium">Ajustar quantidade</span>
                                </label>
                                @if($room['use_manual_tug_qty'] ?? false)
                                <div class="space-y-2 mt-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-medium text-green-800 dark:text-green-300 mb-0.5">Tomadas 100 VA</label>
                                            <input type="text" inputmode="decimal" wire:model="rooms.{{ $i }}.tug_qty_100_manual"
                                                wire:change="calculateRoomLoads({{ $i }})"
                                                @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                                @blur="evaluateCalcFormula($event.target)"
                                                placeholder="Ex: =2"
                                                class="w-full border border-green-300 bg-white dark:bg-gray-800 rounded-lg px-2 py-1 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-green-400">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-green-800 dark:text-green-300 mb-0.5">Tomadas 600 VA</label>
                                            <input type="text" inputmode="decimal" wire:model="rooms.{{ $i }}.tug_qty_600_manual"
                                                wire:change="calculateRoomLoads({{ $i }})"
                                                @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                                @blur="evaluateCalcFormula($event.target)"
                                                placeholder="Ex: =3"
                                                class="w-full border border-green-300 bg-white dark:bg-gray-800 rounded-lg px-2 py-1 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-green-400">
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between pt-0.5">
                                        <button wire:click="resetRoomManual({{ $i }}, 'use_manual_tug_qty')"
                                            class="text-[11px] text-green-600 hover:text-green-800 dark:text-green-300 underline font-medium">
                                            ↺ Restaurar automático
                                        </button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Card: Total Mínimo --}}
                        <div class="bg-yellow-50/80 dark:bg-yellow-900/20 border border-yellow-300 rounded-xl p-3.5 h-full flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between gap-1.5 mb-2 relative" x-data="{ showInfo: false }">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-yellow-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        <span class="text-xs font-bold text-yellow-800 uppercase tracking-wide">Total Mínimo</span>
                                    </div>
                                    <button @click="showInfo = !showInfo" @click.away="showInfo = false" type="button" class="w-5 h-5 rounded-full bg-yellow-200/80 text-yellow-800 text-xs font-bold flex items-center justify-center hover:bg-yellow-300 transition focus:outline-none" title="Informações do Resumo">
                                        i
                                    </button>
                                    <div x-show="showInfo" x-transition.opacity style="display:none;" class="absolute right-0 top-7 z-30 w-64 p-3 bg-white dark:bg-gray-800 text-xs text-gray-700 dark:text-gray-200 rounded-lg shadow-xl border border-yellow-300 leading-relaxed">
                                        <div class="font-bold text-yellow-800 dark:text-yellow-400 mb-1">Resumo do Cômodo</div>
                                        Soma das potências mínimas calculadas de iluminação e tomadas TUG exigidas pela NBR 5410.
                                    </div>
                                </div>
                                <div class="text-2xl font-black text-yellow-900 dark:text-yellow-200">
                                    {{ number_format($totalVa, 0, ',', '.') }} VA
                                </div>
                            </div>

                            {{-- Divider & Summary breakdown --}}
                            <div class="mt-auto pt-3 border-t border-yellow-300/80 dark:border-yellow-700/80 text-xs text-yellow-900 dark:text-yellow-200 space-y-1">
                                <div class="flex justify-between items-center">
                                    <span class="text-yellow-800/90 dark:text-yellow-300/90">Iluminação:</span>
                                    <span class="font-bold">{{ number_format($effectiveLightingVa, 0, ',', '.') }} VA</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-yellow-800/90 dark:text-yellow-300/90">TUG ({{ $effectiveTugQty }} tom.):</span>
                                    <span class="font-bold">{{ number_format($effectiveTugVa, 0, ',', '.') }} VA</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @else
                    {{-- Prompt --}}
                    @if(($room['room_type'] ?? '') && ($room['area_m2'] ?? '') !== '' && ($room['perimeter_m'] ?? '') !== '')
                    <button wire:click="calculateRoomLoads({{ $i }})"
                        class="w-full bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black text-sm font-bold py-2.5 rounded-lg transition">
                        Calcular cargas mínimas (NBR 5410)
                    </button>
                    @else
                    <div class="bg-white dark:bg-gray-800 border border-dashed border-gray-300 dark:border-gray-600 rounded-lg px-4 py-3 text-sm text-gray-400 dark:text-gray-500 text-center">
                        Preencha o tipo de cômodo, área e perímetro para calcular as cargas mínimas.
                    </div>
                    @endif
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-12 text-gray-400 dark:text-gray-500 text-sm">
                Nenhum cômodo cadastrado. Clique em "Adicionar Cômodo" para começar.
            </div>
            @endforelse

            {{-- Floating Action Button (FAB) for adding room --}}
            <div class="fixed bottom-6 right-6 z-40">
                <button wire:click="addRoom" title="Adicionar Cômodo"
                    class="w-14 h-14 rounded-full bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black shadow-xl flex items-center justify-center transition-transform hover:scale-105 active:scale-95 border-2 border-yellow-300 focus:outline-none">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
            </div>
        </div>
        @endif

        {{-- ─── STEP 3 — Levantamento das Cargas ─── --}}
        @if($currentStep === 3)
        <div class="p-4 md:p-8">

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Levantamento das Cargas</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Parametrize cada carga eletricamente. O sistema calcula a corrente de projeto corrigida (Ic) pelos fatores de agrupamento e temperatura.</p>
                </div>
                <div class="flex gap-2 flex-shrink-0">
                    <button wire:click="generateLoadsFromRooms"
                        class="inline-flex items-center gap-1.5 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black text-sm font-bold px-4 py-2.5 rounded-lg transition whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Gerar / Atualizar cargas
                    </button>
                    @if(!empty($loads))
                    <button wire:click="recalculateAllLoads"
                        class="inline-flex items-center gap-1.5 border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 text-sm font-semibold px-3 py-2.5 rounded-lg transition whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Recalcular todas
                    </button>
                    @endif
                </div>
            </div>

            @if(empty($loads))
            {{-- Empty state --}}
            <div class="text-center py-16 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50 dark:bg-gray-900/40">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-4">Nenhuma carga cadastrada ainda.<br>Clique no botão abaixo para gerar automaticamente a partir dos cômodos da Etapa 2.</p>
                <button wire:click="generateLoadsFromRooms"
                    class="inline-flex items-center gap-2 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black text-sm font-bold px-5 py-2.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Gerar cargas dos cômodos
                </button>
            </div>
            @else

            {{-- Summary strip --}}
            @php
                $totalLoads = count($loads);
                $totalVaS3  = array_sum(array_map(fn($l) => (int)($l['power_va'] ?? 0), $loads));
                $roomIdsS3  = array_unique(array_filter(array_column($loads, 'room_id')));
                $tueCount   = count(array_filter($loads, fn($l) => ($l['load_type'] ?? '') === 'TUE'));
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-200">{{ count($roomIdsS3) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Cômodos</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-200">{{ $totalLoads }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wide">Cargas</div>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-orange-800 dark:text-orange-300">{{ $tueCount }}</div>
                    <div class="text-xs text-orange-600 dark:text-orange-400 font-medium uppercase tracking-wide">TUEs</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 rounded-xl p-3 text-center">
                    <div class="text-xl font-black text-brand-black tabular-nums">{{ number_format($totalVaS3, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-600 dark:text-gray-300 font-medium uppercase tracking-wide">VA Total</div>
                </div>
            </div>

            {{-- Group loads by room --}}
            @php
                $loadsByRoomS3 = [];
                foreach ($loads as $li => $load) {
                    $key = (string)($load['room_id'] ?? ('i' . ($load['room_index'] ?? 0)));
                    $loadsByRoomS3[$key][] = ['index' => $li, 'data' => $load];
                }
            @endphp

            @foreach($loadsByRoomS3 as $roomKey => $roomLoads)
            @php
                $firstLoad    = $roomLoads[0]['data'];
                $roomLabelS3  = $firstLoad['room_label'] ?? 'Cômodo';
                $roomIdxS3    = $firstLoad['room_index'] ?? 0;
                $roomTotalVaS3= array_sum(array_map(fn($rl) => (int)($rl['data']['power_va'] ?? 0), $roomLoads));
            @endphp
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm mb-7 overflow-hidden" wire:key="rg-{{ $roomKey }}">

                {{-- Room card header --}}
                <div class="flex items-center justify-between px-5 py-4 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <span class="w-7 h-7 rounded-full bg-brand-yellow text-brand-black text-xs font-black flex items-center justify-center flex-shrink-0">{{ $loop->iteration }}</span>
                        <div>
                            <div class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-tight">{{ $roomLabelS3 }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ count($roomLoads) }} carga{{ count($roomLoads) !== 1 ? 's' : '' }}</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-black text-gray-800 dark:text-gray-200 tabular-nums leading-none">{{ number_format($roomTotalVaS3, 0, ',', '.') }} <span class="text-sm font-semibold text-gray-500 dark:text-gray-400">VA</span></div>
                        <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">total do cômodo</div>
                    </div>
                </div>

                <div class="p-4">
                {{-- Load cards --}}
                @foreach($roomLoads as $rl)
                @php
                    $li   = $rl['index'];
                    $load = $rl['data'];
                    $effectiveVaLoad = ($load['use_manual_va'] ?? false) && ($load['manual_va'] ?? '') !== ''
                        ? (int)$load['manual_va']
                        : (int)($load['power_va'] ?? 0);
                    $typeColorClass = match($load['load_type'] ?? '') {
                        'ILUMINAÇÃO' => 'bg-blue-100 text-blue-800 dark:text-blue-300',
                        'TUG'        => 'bg-green-100 text-green-800 dark:text-green-300',
                        default      => 'bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300',
                    };
                @endphp
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl mb-2 overflow-hidden shadow-sm" wire:key="load-{{ $li }}"
                     x-data="{ open: false }">

                    {{-- Compact header --}}
                    <div class="flex items-center gap-2 px-3 py-2.5 bg-white dark:bg-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition select-none"
                         @click="open = !open">
                        <span class="flex-shrink-0 text-xs font-bold px-2 py-0.5 rounded-full {{ $typeColorClass }}">
                            {{ $load['load_type'] ?? '' }}
                        </span>
                        <span class="text-sm text-gray-700 dark:text-gray-300 flex-1 min-w-0 truncate">
                            @if(($load['load_type'] ?? '') === 'ILUMINAÇÃO')
                                Iluminação
                            @elseif(($load['load_type'] ?? '') === 'TUG')
                                {{ $load['description'] ?: 'TUG' }}
                            @else
                                {{ ($load['description'] ?? '') ?: 'TUE (sem nome)' }}
                            @endif
                            @if($load['use_manual_va'] ?? false)
                                <span class="text-xs text-amber-600 ml-1">*ajustado</span>
                            @endif
                        </span>
                        <span class="flex-shrink-0 text-sm font-bold tabular-nums text-gray-800 dark:text-gray-200">{{ number_format($effectiveVaLoad, 0, ',', '.') }} VA</span>
                        @if(($load['corrected_current_a'] ?? null) !== null)
                        <span class="flex-shrink-0 text-xs font-mono text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 px-2 py-0.5 rounded hidden sm:inline tabular-nums">
                            Ic {{ number_format((float)$load['corrected_current_a'], 2) }} A
                        </span>
                        @endif
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 flex-shrink-0 transition-transform duration-200 ml-1"
                             :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        @if(!($load['is_auto'] ?? true))
                        <button wire:click="removeLoad({{ $li }})" wire:confirm="Remover esta carga?"
                            @click.stop class="flex-shrink-0 p-1 text-gray-400 dark:text-gray-500 hover:text-red-600 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded transition ml-1" title="Remover">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                        @endif
                    </div>

                    {{-- Expanded edit form --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         class="border-t border-gray-100 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/40/50">

                        {{-- TUE fields --}}
                        @if(($load['load_type'] ?? '') === 'TUE')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Equipamento <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="loads.{{ $li }}.description"
                                    placeholder="Ex: Chuveiro elétrico, Ar-condicionado..."
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('loads.'.$li.'.description') border-red-400 bg-red-50 dark:bg-red-900/20 @enderror">
                                @error('loads.'.$li.'.description')<p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Potência (VA) <span class="text-red-500">*</span></label>
                                <input type="text" inputmode="decimal" wire:model="loads.{{ $li }}.power_va"
                                    wire:change="calculateLoad({{ $li }})"
                                    @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                    @blur="evaluateCalcFormula($event.target)"
                                    placeholder="VA (ex: =30*5)"
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 text-center focus:outline-none focus:ring-2 focus:ring-brand-yellow @error('loads.'.$li.'.power_va') border-red-400 bg-red-50 dark:bg-red-900/20 @else border-gray-300 dark:border-gray-600 @enderror">
                                @error('loads.'.$li.'.power_va')<p class="mt-0.5 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        @else
                        {{-- Auto load VA override --}}
                        <div class="flex flex-wrap items-center gap-3 mb-4 px-3 py-2 bg-blue-50 dark:bg-blue-900/20/60 border border-blue-100 rounded-lg">
                            <span class="text-xs text-gray-600 dark:text-gray-300">VA (Etapa 2): <strong>{{ number_format((int)($load['power_va'] ?? 0), 0, ',', '.') }} VA</strong></span>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" wire:model="loads.{{ $li }}.use_manual_va"
                                    wire:change="calculateLoad({{ $li }})"
                                    class="w-3.5 h-3.5 accent-amber-600">
                                <span class="text-xs text-amber-700 dark:text-amber-300 font-medium">Ajustar manualmente</span>
                            </label>
                            @if($load['use_manual_va'] ?? false)
                            <div class="flex items-center gap-1.5">
                                <input type="text" inputmode="decimal" wire:model="loads.{{ $li }}.manual_va"
                                    wire:change="calculateLoad({{ $li }})"
                                    @keydown.enter="evaluateCalcFormula($event.target); $event.target.blur()"
                                    @blur="evaluateCalcFormula($event.target)"
                                    placeholder="VA (ex: =30*5)"
                                    class="w-28 border border-amber-300 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-amber-400">
                                <span class="text-xs text-amber-700 dark:text-amber-300 font-medium">VA</span>
                                <button wire:click="resetLoadManual({{ $li }})" class="text-xs text-amber-500 hover:text-amber-700 dark:text-amber-300 underline">↺ Restaurar</button>
                            </div>
                            @endif
                        </div>
                        @endif

                        {{-- Electrical params --}}
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Fases</label>
                                <select wire:model="loads.{{ $li }}.phases"
                                    wire:change="calculateLoad({{ $li }})"
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                                    <option value="1">1F</option>
                                    <option value="2">2F</option>
                                    <option value="3">3F</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">Tensão (V)</label>
                                <select wire:model="loads.{{ $li }}.voltage_v"
                                    wire:change="calculateLoad({{ $li }})"
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                                    <option value="127">127 V</option>
                                    <option value="220">220 V</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300 mb-1">fp</label>
                                <input type="number" wire:model="loads.{{ $li }}.fp"
                                    wire:change="calculateLoad({{ $li }})"
                                    min="0.01" max="1.00" step="0.01"
                                    class="w-full border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2 py-2 text-sm text-gray-800 dark:text-gray-200 text-center focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                            </div>
                        </div>

                        {{-- Calculation results --}}
                        @if(($load['corrected_current_a'] ?? null) !== null)
                        <div class="grid grid-cols-3 gap-2">
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 text-center">
                                <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">P (W)</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ number_format((float)($load['power_w'] ?? 0), 1) }}</div>
                            </div>
                            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-2.5 text-center">
                                <div class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">I (A)</div>
                                <div class="text-sm font-bold text-gray-800 dark:text-gray-200 tabular-nums">{{ number_format((float)($load['current_a'] ?? 0), 4) }}</div>
                            </div>
                            <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-lg p-2.5 text-center">
                                <div class="text-xs text-purple-500 dark:text-purple-400 font-semibold mb-0.5">Ic (A)</div>
                                <div class="text-sm font-bold text-purple-800 dark:text-purple-300 tabular-nums">{{ number_format((float)($load['corrected_current_a'] ?? 0), 4) }}</div>
                            </div>
                        </div>
                        @else
                        <button wire:click="calculateLoad({{ $li }})"
                            class="w-full text-sm bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold py-2 rounded-lg transition">
                            Calcular corrente corrigida (Ic)
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Add TUE button --}}
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button wire:click="addTueLoad({{ $roomIdxS3 }})"
                        class="inline-flex items-center gap-1.5 text-xs text-orange-700 dark:text-orange-300 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:bg-orange-900/30 border border-orange-200 dark:border-orange-800 px-3 py-1.5 rounded-lg transition font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        + TUE neste cômodo
                    </button>
                </div>
                </div>{{-- /p-4 --}}
            </div>{{-- /room card --}}
            @endforeach

            @endif
        </div>
        @endif

        {{-- ─── STEP 4 — Atribuição de Circuitos ─── --}}
        @if($currentStep === 4)
        <div class="p-6 md:p-8">

            @php
                $s4UnassignedCount = count(array_filter($loads, fn($l) => ($l['circuit_number'] ?? null) === null || $l['circuit_number'] === '' || (int)($l['circuit_number'] ?? 0) <= 0));
                $s4Previews        = $this->getCircuitPreviews();
                $s4HasCritical     = !empty(array_filter($s4Previews, fn($p) => $p['has_critical_error']));
                $s4HasSevere       = !empty(array_filter($s4Previews, fn($p) => $p['status'] === 'severe'));

                // Group loads by room for the assignment table
                $s4LoadsByRoom = [];
                foreach ($loads as $li => $load) {
                    $rid = (string)($load['room_id'] ?? 'null');
                    $s4LoadsByRoom[$rid][] = ['li' => $li, 'load' => $load];
                }
            @endphp

            {{-- Header --}}
            <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200">Atribuição de Circuitos</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Agrupe as cargas em circuitos. Cada carga precisa de um número de circuito.</p>
                </div>
                @if(!empty($loads))
                <div class="flex flex-wrap items-center gap-2">
                    <button wire:click="resetCircuitAssignments"
                        wire:confirm="Resetar todos os circuitos? Todas as cargas ficarao sem circuito atribuido."
                        class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-red-200 dark:border-red-800 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-700 dark:text-red-300 font-bold text-sm px-4 py-2.5 rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16m-1 0l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4h6v3"/></svg>
                        Resetar circuitos
                    </button>
                    <button wire:click="openDistributeModal"
                        class="inline-flex items-center gap-2 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold text-sm px-4 py-2.5 rounded-lg transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        Distribuir automaticamente
                    </button>
                </div>
                @endif
            </div>

            @if(empty($loads))
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 rounded-xl p-5 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Nenhuma carga cadastrada. Volte à Etapa 3 e cadastre pelo menos uma carga antes de prosseguir.</span>
                </div>
            @else

            {{-- Stats bar --}}
            <div class="grid grid-cols-3 gap-3 mb-5">
                <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-gray-800 dark:text-gray-200">{{ count($loads) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Cargas</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black {{ $s4UnassignedCount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ count($loads) - $s4UnassignedCount }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Atribuídas</div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/40 border border-gray-200 dark:border-gray-700 rounded-xl p-3 text-center">
                    <div class="text-2xl font-black text-brand-black">{{ count($s4Previews) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Circuitos</div>
                </div>
            </div>

            {{-- Alerts --}}
            @if($s4HasCritical)
            <div class="mb-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 rounded-xl p-4 text-sm">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <span><strong>Erros críticos detectados.</strong> Existem circuitos com tensões ou fases mistas. Corrija antes de avançar.</span>
            </div>
            @endif
            @if($s4UnassignedCount > 0)
            <div class="mb-4 flex items-start gap-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 rounded-xl p-4 text-sm">
                <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span><strong>{{ $s4UnassignedCount }} carga(s) sem circuito.</strong> Atribua um número de circuito a todas as cargas antes de avançar.</span>
            </div>
            @endif

            {{-- ══ LOAD ASSIGNMENT TABLE (grouped by room) ══ --}}
            <div class="mb-8">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">Atribuição por Cômodo</h4>

                @foreach($s4LoadsByRoom as $s4RoomKey => $s4RoomLoads)
                @php
                    $s4FirstLoad  = $s4RoomLoads[0]['load'];
                    $s4RoomLabel  = $s4FirstLoad['room_label'] ?: '—';
                @endphp
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm mb-4 overflow-hidden">
                    {{-- Room header --}}
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                        <span class="w-6 h-6 rounded-full bg-brand-yellow text-brand-black text-xs font-black flex items-center justify-center flex-shrink-0">
                            {{ array_search($s4RoomKey, array_keys($s4LoadsByRoom)) + 1 }}
                        </span>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $s4RoomLabel }}</span>
                        <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ count($s4RoomLoads) }} carga(s)</span>
                    </div>

                    {{-- Load rows --}}
                    <div class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($s4RoomLoads as $s4Item)
                        @php
                            $s4Li   = $s4Item['li'];
                            $s4Load = $s4Item['load'];
                            $s4Type = $s4Load['load_type'] ?? 'ILUMINAÇÃO';
                            $s4Va   = ($s4Load['use_manual_va'] ?? false) && ($s4Load['manual_va'] ?? '') !== ''
                                ? (int)$s4Load['manual_va']
                                : (int)($s4Load['power_va'] ?? 0);
                            $s4HasCircuit = !empty($s4Load['circuit_number']) && (int)($s4Load['circuit_number'] ?? 0) > 0;
                        @endphp
                        <div wire:key="s4-load-{{ $s4Li }}-{{ $s4Va }}"
                             x-data="{
                                splitOpen: false,
                                splitCount: 2,
                                splitTotalVa: {{ $s4Va }},
                                splitVas: [],
                                get splitSum() { return this.splitVas.reduce((a,b)=>a+(+b||0),0); },
                                init() { this.updateSplitVas(); },
                                updateSplitVas() {
                                    let eq = Math.round(this.splitTotalVa / this.splitCount);
                                    this.splitVas = Array.from({length: this.splitCount}, (_, i) =>
                                        i === this.splitCount - 1
                                            ? Math.max(1, this.splitTotalVa - eq * (this.splitCount - 1))
                                            : eq
                                    );
                                }
                             }">

                            {{-- Main row --}}
                            <div class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">

                                {{-- Type badge --}}
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0
                                    {{ $s4Type === 'ILUMINAÇÃO' ? 'bg-blue-100 text-blue-700 dark:text-blue-300' :
                                       ($s4Type === 'TUG' ? 'bg-green-100 text-green-700 dark:text-green-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300') }}">
                                    {{ $s4Type === 'ILUMINAÇÃO' ? 'ILUM' : $s4Type }}
                                </span>

                                {{-- Description + VA --}}
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                                        {{ $s4Load['description'] ?: '—' }}
                                    </div>
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        {{ number_format($s4Va, 0, ',', '.') }}VA
                                        · {{ $s4Load['voltage_v'] ?? 127 }}V
                                        · {{ $s4Load['phases'] ?? 1 }}F
                                        @if(($s4Load['corrected_current_a'] ?? null) !== null)
                                            · Ic={{ number_format((float)$s4Load['corrected_current_a'], 2, ',', '.') }}A
                                        @endif
                                    </div>
                                </div>

                                {{-- Circuit number input + split button --}}
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">Circ.</span>
                                    <input type="number"
                                           wire:model.lazy="loads.{{ $s4Li }}.circuit_number"
                                           min="1"
                                           placeholder="—"
                                           class="w-14 text-center text-sm font-bold border rounded-lg py-1.5 px-2 focus:outline-none focus:ring-2 transition
                                               {{ $s4HasCircuit
                                                   ? 'border-green-300 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-300 focus:ring-green-300'
                                                   : 'border-red-300 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 focus:ring-red-300' }}">

                                    {{-- Split button --}}
                                    @if($s4Va > 0)
                                    <button @click="splitOpen = !splitOpen"
                                        :class="splitOpen ? 'bg-amber-100 border-amber-300 text-amber-700 dark:text-amber-300' : 'border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 hover:text-amber-600 hover:border-amber-300 hover:bg-amber-50 dark:bg-amber-900/20'"
                                        title="Dividir esta carga em múltiplos circuitos"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border text-sm font-bold transition flex-shrink-0">
                                        ÷
                                    </button>
                                    @endif

                                    {{-- Reagrupar button (only on split parts) --}}
                                    @if(preg_match('/— pt \d+\/\d+$/', $s4Load['description'] ?? ''))
                                    <button wire:click="mergeLoadParts({{ $s4Li }})"
                                        title="Reagrupar todas as partes desta divisão numa única carga"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-indigo-200 dark:border-indigo-800 text-indigo-400 hover:text-indigo-700 dark:text-indigo-300 hover:border-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 transition flex-shrink-0 text-sm">
                                        ↩
                                    </button>
                                    @endif
                                </div>
                            </div>

                            {{-- Split panel --}}
                            <div x-show="splitOpen"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-1"
                                 class="mx-4 mb-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-3">

                                {{-- Header row: title + counter --}}
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs font-bold text-amber-800 uppercase tracking-wide">Dividir em partes</span>
                                    <div class="flex items-center gap-1">
                                        <button @click="splitCount = Math.max(2, splitCount - 1); updateSplitVas()"
                                            class="w-6 h-6 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-900 text-sm font-bold flex items-center justify-center transition">−</button>
                                        <span class="w-5 text-center text-sm font-black text-amber-900" x-text="splitCount"></span>
                                        <button @click="splitCount = Math.min(8, splitCount + 1); updateSplitVas()"
                                            class="w-6 h-6 rounded-lg bg-amber-200 hover:bg-amber-300 text-amber-900 text-sm font-bold flex items-center justify-center transition">+</button>
                                        <span class="text-xs text-amber-700 dark:text-amber-300 ml-1">partes</span>
                                    </div>
                                </div>

                                {{-- VA inputs per part --}}
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mb-3">
                                    <template x-for="(va, idx) in splitVas" :key="idx">
                                        <div>
                                            <div class="text-xs text-amber-700 dark:text-amber-300 mb-1" x-text="'Parte ' + (idx + 1)"></div>
                                            <div class="flex items-center gap-1">
                                                <input type="number"
                                                       x-model.number="splitVas[idx]"
                                                       min="1"
                                                       class="w-full border border-amber-200 dark:border-amber-800 rounded-lg px-2 py-1.5 text-sm text-center font-semibold bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-amber-300">
                                                <span class="text-xs text-amber-600 flex-shrink-0">VA</span>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Footer: total check + buttons --}}
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs transition-colors" :class="splitSum !== splitTotalVa ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-amber-700 dark:text-amber-300'">
                                        Soma: <strong x-text="splitSum"></strong>
                                        / <strong>{{ $s4Va }}</strong> VA
                                        <span x-show="splitSum !== splitTotalVa"> — a soma deve ser exatamente {{ $s4Va }}VA</span>
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <button @click="splitOpen = false"
                                            class="text-xs px-3 py-1.5 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                            Cancelar
                                        </button>
                                        <button @click="if(splitSum === splitTotalVa){ $wire.splitLoad({{ $s4Li }}, splitCount, JSON.stringify(splitVas.map(v => +v || 0))); splitOpen = false; }"
                                            :disabled="splitSum !== splitTotalVa"
                                            :class="splitSum !== splitTotalVa ? 'opacity-40 cursor-not-allowed bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400' : 'bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black'"
                                            class="text-xs px-3 py-1.5 font-bold rounded-lg transition">
                                            Confirmar divisão
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>{{-- /Alpine row --}}
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            {{-- ══ CIRCUIT PREVIEWS ══ --}}
            @if(!empty($s4Previews))
            <div>
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">Prévia dos Circuitos</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($s4Previews as $s4P)
                    @php
                        $s4PStatus = $s4P['status'];
                        $s4PColorBorder = match($s4PStatus) {
                            'critical' => 'border-red-300',
                            'severe'   => 'border-orange-300 dark:border-orange-700',
                            'warning'  => 'border-yellow-300',
                            default    => 'border-gray-200 dark:border-gray-700',
                        };
                        $s4PColorHeader = match($s4PStatus) {
                            'critical' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800',
                            'severe'   => 'bg-orange-50 dark:bg-orange-900/20 border-orange-200 dark:border-orange-800',
                            'warning'  => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
                            default    => 'bg-gray-50 dark:bg-gray-900/40 border-gray-200 dark:border-gray-700',
                        };
                        $s4PTypeColor = match($s4P['circuit_type']) {
                            'ILUMINAÇÃO' => 'bg-blue-100 text-blue-700 dark:text-blue-300',
                            'TUG'        => 'bg-green-100 text-green-700 dark:text-green-300',
                            'TUE'        => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300',
                            default      => 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300',
                        };
                    @endphp
                    <div class="border {{ $s4PColorBorder }} rounded-2xl overflow-hidden shadow-sm">

                        {{-- Card header --}}
                        <div class="flex items-center justify-between px-4 py-3 {{ $s4PColorHeader }} border-b">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-7 h-7 rounded-full bg-brand-yellow text-brand-black text-xs font-black flex items-center justify-center flex-shrink-0">
                                    {{ $s4P['circuit_number'] }}
                                </span>
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full flex-shrink-0 {{ $s4PTypeColor }}">
                                    {{ $s4P['circuit_type'] }}
                                </span>
                                @if(!empty($s4P['part_label']))
                                <span class="text-xs text-gray-500 dark:text-gray-400 font-medium truncate">{{ $s4P['part_label'] }}</span>
                                @endif
                            </div>
                            @if($s4PStatus !== 'ok')
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full
                                {{ $s4PStatus === 'critical' ? 'bg-red-100 text-red-700 dark:text-red-300' :
                                   ($s4PStatus === 'severe' ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ strtoupper($s4PStatus) }}
                            </span>
                            @else
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:text-green-300">OK</span>
                            @endif
                        </div>

                        {{-- Room labels strip --}}
                        @if(!empty($s4P['room_labels']))
                        <div class="px-4 py-2 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 flex flex-wrap gap-1">
                            @foreach($s4P['room_labels'] as $s4RoomLbl)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">{{ $s4RoomLbl }}</span>
                            @endforeach
                        </div>
                        @endif

                        {{-- Card body --}}
                        <div class="p-4 bg-white dark:bg-gray-800">

                            {{-- Electrical values --}}
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm mb-3">
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">VA Total</span>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($s4P['total_power_va'], 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">W Total</span>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($s4P['total_power_w'], 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">Tensão / Fases</span>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $s4P['voltage_v'] }}V / {{ $s4P['phases'] }}F</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">Corrente</span>
                                    <div class="font-bold text-purple-700 dark:text-purple-300">{{ number_format($s4P['current_a'], 2, ',', '.') }} A</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">fp result.</span>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($s4P['resulting_fp'], 2, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-400 dark:text-gray-500 text-xs">Cargas</span>
                                    <div class="font-semibold text-gray-800 dark:text-gray-200">{{ $s4P['total_loads'] }}</div>
                                </div>
                            </div>

                            {{-- Loads in this circuit --}}
                            <div class="flex flex-wrap gap-1 mb-3">
                                @foreach($s4P['loads'] as $s4PLoad)
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium whitespace-nowrap"
                                      title="{{ $s4PLoad['description'] ?: $s4PLoad['load_type'] }}">
                                    {{ $s4PLoad['description'] ?: $s4PLoad['load_type'] }}
                                </span>
                                @endforeach
                            </div>

                            {{-- Alerts --}}
                            @if(!empty($s4P['alerts']))
                            <div class="space-y-1">
                                @foreach($s4P['alerts'] as $s4Alert)
                                <div class="flex items-start gap-1.5 text-xs rounded-lg p-2
                                    {{ $s4Alert['level'] === 'critical' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300' :
                                       ($s4Alert['level'] === 'severe' ? 'bg-orange-50 dark:bg-orange-900/20 text-orange-700 dark:text-orange-300' : 'bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700') }}">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="{{ $s4Alert['level'] === 'critical'
                                                ? 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z'
                                                : 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }}"/>
                                    </svg>
                                    {{ $s4Alert['msg'] }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @elseif(!empty($loads))
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 rounded-xl p-4 text-sm flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Nenhum circuito atribuído ainda. Clique em <strong>"Distribuir automaticamente"</strong> ou preencha o número do circuito em cada carga.</span>
            </div>
            @endif

            @endif
        </div>
        @endif

        {{-- ─── STEP 5 — Memorial de Cálculo e Dimensionamento ─── --}}
        @if($currentStep === 5)
        <div class="p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Memorial de Cálculo e Dimensionamento</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Detalhamento dos cálculos e ajuste de parâmetros reais de cada circuito (comprimento, método, agrupamento, temperatura e overrides manuais).</p>

            @php
                $calcRows = \Illuminate\Support\Facades\DB::table('project_circuit_calculations')
                    ->where('project_id', $projectId)
                    ->orderBy('circuit_number')
                    ->get();
            @endphp

            @if($calcRows->isEmpty())
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 text-yellow-800 rounded-lg p-4 text-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Nenhum circuito calculado. Volte à Etapa 4 e atribua os circuitos.</span>
                </div>
            @else
                <div class="space-y-6">
                    @foreach($calcRows as $calcRow)
                    @php
                        $cn = (int) $calcRow->circuit_number;
                        $ov = $circuitOverrides[$cn] ?? [
                            'distance_m'           => (float)($calcRow->distance_m ?? 0.0),
                            'installation_method'  => (string)($calcRow->installation_method ?? 'B1'),
                            'temperature_c'        => (int)($calcRow->temperature_c ?? 30),
                            'grouped_circuits'     => (int)($calcRow->grouped_circuits ?? 1),
                            'voltage_drop_percent' => (float)($calcRow->voltage_drop_percent ?? 4.0),
                            'use_manual_conductor' => (bool)($calcRow->use_manual_final_conductor ?? false),
                            'manual_conductor_mm2' => $calcRow->final_conductor_manual_mm2 !== null ? (string)$calcRow->final_conductor_manual_mm2 : '',
                            'use_manual_breaker'   => (bool)($calcRow->use_manual_breaker ?? false),
                            'manual_breaker_a'     => $calcRow->breaker_manual_a !== null ? (string)$calcRow->breaker_manual_a : '',
                        ];
                    @endphp
                    <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-gray-800">
                        {{-- Circuit Header --}}
                        <div class="bg-gray-50 dark:bg-gray-900/40 px-4 py-3 flex flex-wrap items-center gap-3 border-b border-gray-200 dark:border-gray-700">
                            <span class="bg-brand-yellow text-brand-black text-xs font-black px-2.5 py-1 rounded-full">C{{ $calcRow->circuit_number }}</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 text-sm">{{ $calcRow->description ?? '—' }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold
                                {{ $calcRow->circuit_type === 'ILUMINAÇÃO' ? 'bg-blue-100 text-blue-700 dark:text-blue-300' :
                                   ($calcRow->circuit_type === 'TUG' ? 'bg-green-100 text-green-700 dark:text-green-300' : 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300') }}">
                                {{ $calcRow->circuit_type ?? '—' }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ $calcRow->phases ?? '?' }}F · {{ $calcRow->voltage ?? '?' }}V</span>
                            <span class="ml-auto text-xs font-bold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 px-2.5 py-1 rounded-full border border-purple-200 dark:border-purple-800">
                                Iproj: {{ number_format($calcRow->project_current_a ?? 0, 2, ',', '.') }} A
                            </span>
                        </div>

                        <div class="p-4 space-y-4">
                            {{-- Painel de Edição de Parâmetros --}}
                            <div class="bg-amber-50/50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/50 rounded-xl p-3.5">
                                <div class="text-xs font-bold text-amber-800 dark:text-amber-300 uppercase tracking-wide mb-2.5 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Parâmetros do Circuito (Recálculo Automático)
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                                    {{-- Comprimento L --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Comprimento (m)</label>
                                        <input type="number" step="0.5" min="0"
                                            wire:change="updateCircuitParam({{ $cn }}, 'distance_m', $event.target.value)"
                                            value="{{ $ov['distance_m'] ?? 0 }}"
                                            class="w-full text-xs font-bold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2.5 py-1.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400">
                                    </div>
                                    {{-- Método de Instalação --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Método Instalação</label>
                                        <select wire:change="updateCircuitParam({{ $cn }}, 'installation_method', $event.target.value)"
                                            class="w-full text-xs font-bold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400">
                                            @foreach(['A1','A2','B1','B2','C','D'] as $m)
                                                <option value="{{ $m }}" {{ ($ov['installation_method'] ?? 'B1') === $m ? 'selected' : '' }}>{{ $m }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Temperatura °C --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Temp. Ambiente (°C)</label>
                                        <select wire:change="updateCircuitParam({{ $cn }}, 'temperature_c', $event.target.value)"
                                            class="w-full text-xs font-bold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400">
                                            @foreach([15, 20, 25, 30, 35, 40, 45, 50] as $t)
                                                <option value="{{ $t }}" {{ (int)($ov['temperature_c'] ?? 30) === $t ? 'selected' : '' }}>{{ $t }} °C</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Circ. Agrupados --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Circ. Agrupados</label>
                                        <input type="number" min="1" max="20"
                                            wire:change="updateCircuitParam({{ $cn }}, 'grouped_circuits', $event.target.value)"
                                            value="{{ $ov['grouped_circuits'] ?? 1 }}"
                                            class="w-full text-xs font-bold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2.5 py-1.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400">
                                    </div>
                                    {{-- Queda de Tensão % --}}
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Queda Tensão Max %</label>
                                        <select wire:change="updateCircuitParam({{ $cn }}, 'voltage_drop_percent', $event.target.value)"
                                            class="w-full text-xs font-bold border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-lg px-2 py-1.5 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-amber-400">
                                            @foreach([2.0, 3.0, 4.0, 5.0, 7.0] as $vd)
                                                <option value="{{ $vd }}" {{ (float)($ov['voltage_drop_percent'] ?? 4.0) == $vd ? 'selected' : '' }}>{{ $vd }} %</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Resumo do Memorial (Valores Calculados) --}}
                            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-xs">
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px]">Fator Temp (fT)</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ number_format($calcRow->temperature_factor ?? 1, 2, ',', '.') }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px]">Fator Agrup (fg)</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ number_format($calcRow->grouping_factor ?? 1, 2, ',', '.') }}</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px]">Corrente Corrigida (Ic)</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ number_format($calcRow->corrected_current_a ?? 0, 2, ',', '.') }} A</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px]">Cabo Mínimo (Norma)</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $calcRow->min_conductor_mm2 ?? '—' }} mm²</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px]">Cabo Ampacidade</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $calcRow->ampacity_conductor_mm2 ?? '—' }} mm²</span>
                                </div>
                                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg p-2.5">
                                    <span class="text-gray-400 dark:text-gray-500 block text-[10px]">Cabo Queda ({{ $calcRow->distance_m ?? 0 }}m)</span>
                                    <span class="font-bold text-gray-800 dark:text-gray-200">{{ $calcRow->voltage_drop_conductor_mm2 ?? '—' }} mm²</span>
                                </div>
                            </div>

                            {{-- Sobrescritas de Cabo e Disjuntor --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                {{-- Cabo Final --}}
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-3.5">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-bold text-blue-900 dark:text-blue-200">Seção do Cabo Final</span>
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox"
                                                wire:model.live="circuitOverrides.{{ $cn }}.use_manual_conductor"
                                                wire:change="saveStep5"
                                                class="w-4 h-4 rounded accent-blue-600">
                                            <span class="text-xs text-blue-700 dark:text-blue-300 font-bold">Manual</span>
                                        </label>
                                    </div>
                                    <p class="text-xs text-blue-600 dark:text-blue-400 mb-2">Calculado: <strong>{{ $calcRow->final_conductor_calculated_mm2 ?? '—' }} mm²</strong> | Iz: {{ $calcRow->iz_a !== null ? number_format($calcRow->iz_a, 1, ',', '.') . 'A' : '—' }}</p>
                                    @if(!empty($ov['use_manual_conductor']))
                                    <select wire:model.live="circuitOverrides.{{ $cn }}.manual_conductor_mm2"
                                        wire:change="saveStep5"
                                        class="w-full border border-blue-300 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 font-bold focus:outline-none focus:ring-2 focus:ring-blue-400">
                                        <option value="">Selecione a seção...</option>
                                        @foreach([1.5, 2.5, 4.0, 6.0, 10.0, 16.0, 25.0, 35.0, 50.0, 70.0, 95.0] as $mm2Val)
                                            <option value="{{ $mm2Val }}">{{ $mm2Val }} mm²</option>
                                        @endforeach
                                    </select>
                                    @else
                                        <div class="text-2xl font-black text-blue-800 dark:text-blue-300">{{ $calcRow->final_conductor_calculated_mm2 ?? '—' }} mm²</div>
                                    @endif
                                </div>

                                {{-- Disjuntor --}}
                                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-3.5">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-bold text-purple-900 dark:text-purple-200">Disjuntor de Proteção</span>
                                        <label class="flex items-center gap-1.5 cursor-pointer">
                                            <input type="checkbox"
                                                wire:model.live="circuitOverrides.{{ $cn }}.use_manual_breaker"
                                                wire:change="saveStep5"
                                                class="w-4 h-4 rounded accent-purple-600">
                                            <span class="text-xs text-purple-700 dark:text-purple-300 font-bold">Manual</span>
                                        </label>
                                    </div>
                                    <p class="text-xs text-purple-600 dark:text-purple-400 mb-2">Calculado: <strong>{{ $calcRow->breaker_calculated_a ?? '—' }} A</strong></p>
                                    @if(!empty($ov['use_manual_breaker']))
                                    <select wire:model.live="circuitOverrides.{{ $cn }}.manual_breaker_a"
                                        wire:change="saveStep5"
                                        class="w-full border border-purple-300 dark:border-purple-700 bg-white dark:bg-gray-800 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 font-bold focus:outline-none focus:ring-2 focus:ring-purple-400">
                                        <option value="">Selecione a ampermagem...</option>
                                        @foreach([6, 10, 16, 20, 25, 32, 40, 50, 63, 80, 100, 125] as $baVal)
                                            <option value="{{ $baVal }}">{{ $baVal }} A</option>
                                        @endforeach
                                    </select>
                                    @else
                                        <div class="text-2xl font-black text-purple-800 dark:text-purple-300">{{ $calcRow->breaker_calculated_a ?? '—' }} A</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endif

        {{-- ─── STEP 6 — Distribuição de Fases ─── --}}
        @if($currentStep === 6)
        <div class="p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Distribuição de Fases</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Atribua cada circuito a uma fase (R, S ou T) para equilibrar a instalação.</p>

            @php
                $calcRows = \Illuminate\Support\Facades\DB::table('project_circuit_calculations')
                    ->where('project_id', $projectId)
                    ->orderBy('circuit_number')
                    ->get();

                $totalR = $totalS = $totalT = 0.0;
                foreach ($calcRows as $phRow) {
                    $cn8    = (int) $phRow->circuit_number;
                    $ph8    = (int) ($phRow->phases ?? 1);
                    $va8    = (float) ($phRow->power_va ?? 0);
                    $asgn8  = $phaseAssignments[$cn8] ?? 'r';
                    if ($ph8 === 3) {
                        $totalR += $va8 / 3;
                        $totalS += $va8 / 3;
                        $totalT += $va8 / 3;
                    } else {
                        if ($asgn8 === 'r') $totalR += $va8;
                        elseif ($asgn8 === 's') $totalS += $va8;
                        else $totalT += $va8;
                    }
                }
                $maxPh = max($totalR, $totalS, $totalT);
                $minPh = $maxPh > 0 ? min($totalR, $totalS, $totalT) : 0;
                $imbPct = $maxPh > 0 ? (($maxPh - $minPh) / $maxPh) * 100 : 0;
            @endphp

            <div class="flex flex-wrap items-center gap-3 mb-5">
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg px-4 py-2 text-center">
                    <p class="text-xs font-bold text-red-600 dark:text-red-400">R</p>
                    <p class="font-bold text-red-800 dark:text-red-300">{{ number_format($totalR, 0, ',', '.') }} VA</p>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg px-4 py-2 text-center">
                    <p class="text-xs font-bold text-yellow-600">S</p>
                    <p class="font-bold text-yellow-800">{{ number_format($totalS, 0, ',', '.') }} VA</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg px-4 py-2 text-center">
                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400">T</p>
                    <p class="font-bold text-blue-800 dark:text-blue-300">{{ number_format($totalT, 0, ',', '.') }} VA</p>
                </div>
                <div class="rounded-lg px-4 py-2 text-center {{ $imbPct > 20 ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : ($imbPct > 10 ? 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' : 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800') }}">
                    <p class="text-xs font-bold {{ $imbPct > 20 ? 'text-red-600 dark:text-red-400' : ($imbPct > 10 ? 'text-yellow-600' : 'text-green-600 dark:text-green-400') }}">Desequilíbrio</p>
                    <p class="font-bold {{ $imbPct > 20 ? 'text-red-800 dark:text-red-300' : ($imbPct > 10 ? 'text-yellow-800' : 'text-green-800 dark:text-green-300') }}">{{ number_format($imbPct, 1, ',', '.') }}%</p>
                </div>
                <button wire:click="autoAssignPhases" wire:loading.attr="disabled" wire:target="autoAssignPhases"
                    class="ml-auto inline-flex items-center gap-2 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold text-sm px-4 py-2.5 rounded-lg transition disabled:opacity-60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Auto-Balancear
                </button>
            </div>

            @if($calcRows->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 italic">Nenhum circuito calculado.</p>
            @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/40 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Circ.</th>
                            <th class="px-3 py-3 text-left font-semibold text-gray-600 dark:text-gray-300">Ambiente</th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-600 dark:text-gray-300">VA</th>
                            <th class="px-3 py-3 text-center font-semibold text-gray-600 dark:text-gray-300">Fases</th>
                            <th class="px-3 py-3 text-center font-semibold text-red-500">R</th>
                            <th class="px-3 py-3 text-center font-semibold text-yellow-500">S</th>
                            <th class="px-3 py-3 text-center font-semibold text-blue-500">T</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($calcRows as $calcRow)
                        @php
                            $cn8   = (int) $calcRow->circuit_number;
                            $ph8   = (int) ($calcRow->phases ?? 1);
                            $asgn8 = $phaseAssignments[$cn8] ?? 'r';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 py-2.5">
                                <span class="bg-brand-yellow text-brand-black text-xs font-black px-2 py-0.5 rounded">C{{ $calcRow->circuit_number }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300 text-xs">{{ $calcRow->description ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-right text-gray-700 dark:text-gray-300">{{ number_format($calcRow->power_va ?? 0, 0, ',', '.') }}</td>
                            <td class="px-3 py-2.5 text-center">
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-1.5 py-0.5 rounded font-medium">{{ $ph8 }}F</span>
                            </td>
                            @if($ph8 === 3)
                                <td colspan="3" class="px-3 py-2.5 text-center text-xs text-gray-400 dark:text-gray-500 italic">Trifásico — R+S+T automático</td>
                            @else
                                <td class="px-3 py-2.5 text-center">
                                    <input type="radio" name="ph-{{ $cn8 }}" value="r"
                                        wire:model="phaseAssignments.{{ $cn8 }}"
                                        class="w-4 h-4 accent-red-500 cursor-pointer">
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <input type="radio" name="ph-{{ $cn8 }}" value="s"
                                        wire:model="phaseAssignments.{{ $cn8 }}"
                                        class="w-4 h-4 accent-yellow-500 cursor-pointer">
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <input type="radio" name="ph-{{ $cn8 }}" value="t"
                                        wire:model="phaseAssignments.{{ $cn8 }}"
                                        class="w-4 h-4 accent-blue-500 cursor-pointer">
                                </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- ─── STEP 7 — Eletrodutos / DPS / IDR ─── --}}
        @if($currentStep === 7)
        <div class="p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Eletrodutos / DPS / IDR</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Dimensionamento do eletroduto principal, dispositivo de proteção contra surtos e interruptor diferencial-residual.</p>

            @php
                $calcRows9 = \Illuminate\Support\Facades\DB::table('project_circuit_calculations')
                    ->where('project_id', $projectId)
                    ->orderBy('circuit_number')
                    ->get();

                // Build cable counts for conduit
                $keyMap9 = [1.5=>'1_5',2.5=>'2_5',4.0=>'4',6.0=>'6',10.0=>'10',16.0=>'16',25.0=>'25',35.0=>'35',50.0=>'50',70.0=>'70',95.0=>'95'];
                $cableCounts9 = array_fill_keys(array_values($keyMap9), 0);
                $maxBreakerA9 = 0;

                foreach ($calcRows9 as $row9) {
                    $useMnl9 = (bool) ($row9->use_manual_final_conductor ?? false);
                    $mm29    = $useMnl9 && $row9->final_conductor_manual_mm2
                        ? (float) $row9->final_conductor_manual_mm2
                        : (float) ($row9->final_conductor_calculated_mm2 ?? 0);
                    $ph9     = (int) ($row9->phases ?? 1);
                    $key9    = $keyMap9[$mm29] ?? '2_5';
                    $cnt9    = $ph9 === 3 ? 4 : 3;
                    $cableCounts9[$key9] += $cnt9;

                    $useMnlBr9 = (bool) ($row9->use_manual_breaker ?? false);
                    $ba9 = $useMnlBr9 && $row9->breaker_manual_a ? (int) $row9->breaker_manual_a : (int) ($row9->breaker_calculated_a ?? 0);
                    if ($ba9 > $maxBreakerA9) $maxBreakerA9 = $ba9;
                }

                $conduitResult9 = app(\App\Services\Calculation\ConduitService::class)->calculate($cableCounts9);
                $dpsResult9     = app(\App\Services\Calculation\DpsService::class)->calculate($dpsLocationType, $dpsVoltage, $shortCircuitKa);
                $idrResult9     = app(\App\Services\Calculation\IdrService::class)->calculate($sePhases ?: $defaultPhases, $hasNeutral, $shortCircuitKa, $maxBreakerA9);
            @endphp

            {{-- Parâmetros gerais --}}
            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700 p-4 mb-5">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Parâmetros de Entrada</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Icc (kA)</label>
                        <input type="number" wire:model.lazy="shortCircuitKa" min="1" max="100" step="0.5"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fases (IDR)</label>
                        <select wire:model.lazy="sePhases"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                            @foreach([1, 2, 3] as $phOpt)
                                <option value="{{ $phOpt }}">{{ $phOpt }} fase{{ $phOpt > 1 ? 's' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Neutro?</label>
                        <select wire:model.lazy="hasNeutral"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                            <option value="SIM">Sim</option>
                            <option value="NÃO">Não</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tensão DPS (V)</label>
                        <select wire:model.lazy="dpsVoltage"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                            @foreach([127, 220, 380] as $vOpt)
                                <option value="{{ $vOpt }}">{{ $vOpt }}V</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-4">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Localização DPS</label>
                        <select wire:model.lazy="dpsLocationType"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                            <option value="Descargas diretas">Descargas Diretas (Classe I)</option>
                            <option value="Descargas Indiretas">Descargas Indiretas (Classe II)</option>
                            <option value="Proteção Individual Complementar">Proteção Individual Complementar (Classe III)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Resultados em cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- Eletroduto --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="bg-gray-700 text-white px-4 py-2.5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span class="font-semibold text-sm">Eletroduto</span>
                    </div>
                    <div class="p-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Cabos totais</span>
                            <span class="font-semibold">{{ $conduitResult9['total_cables'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Diâmetro</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 text-base">
                                {{ $conduitResult9['dimension_mm'] ? $conduitResult9['dimension_mm'] . ' mm (' . $conduitResult9['dimension_inch'] . ')' : 'N/D' }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Ocupação</span>
                            <span class="font-semibold {{ $conduitResult9['used_conduit'] > 40 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                {{ number_format($conduitResult9['used_conduit'], 1, ',', '.') }}% / 40%
                            </span>
                        </div>
                        @if($conduitResult9['notes'])
                        <p class="text-xs text-amber-600 italic">{{ $conduitResult9['notes'] }}</p>
                        @endif
                    </div>
                </div>

                {{-- DPS --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="bg-orange-600 text-white px-4 py-2.5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="font-semibold text-sm">DPS — Classe {{ $dpsResult9['dps_class'] }}</span>
                    </div>
                    <div class="p-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Up máx.</span>
                            <span class="font-semibold">{{ $dpsResult9['min_up'] }} kV</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Uc</span>
                            <span class="font-semibold">{{ $dpsResult9['uc_v'] }} V</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">In</span>
                            <span class="font-semibold">{{ $dpsResult9['in_value'] }} kA</span>
                        </div>
                        @if($dpsResult9['iimp'])
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Iimp</span>
                            <span class="font-semibold">{{ number_format($dpsResult9['iimp'] / 1000, 1, ',', '.') }} kA</span>
                        </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Icc selecionado</span>
                            <span class="font-semibold">{{ $dpsResult9['icc'] }} kA</span>
                        </div>
                    </div>
                </div>

                {{-- IDR --}}
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="bg-indigo-600 text-white px-4 py-2.5 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="font-semibold text-sm">IDR</span>
                    </div>
                    <div class="p-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">In</span>
                            <span class="font-bold text-gray-800 dark:text-gray-200 text-base">{{ $idrResult9['nominal_current_a'] ?? '—' }} A</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Polos</span>
                            <span class="font-semibold">{{ $idrResult9['poles'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Corrente residual</span>
                            <span class="font-semibold">{{ $idrResult9['residual_current'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Icc selecionado</span>
                            <span class="font-semibold">{{ $idrResult9['icc'] }} kA</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Status</span>
                            <span class="text-xs font-semibold {{ $idrResult9['idr_status'] === 'OK' ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                                {{ $idrResult9['idr_status'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-400 dark:text-gray-500 mt-4 italic">* Os valores são recalculados automaticamente ao alterar os parâmetros. Clique em "Próximo" para salvar.</p>
        </div>
        @endif

        {{-- ─── STEP 8 — Padrão de Entrada ─── --}}
        @if($currentStep === 8)
        <div class="p-6 md:p-8">
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-1">Padrão de Entrada</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">Resumo final da instalação com carga instalada e demanda provável.</p>

            @php
                $calcRows10 = \Illuminate\Support\Facades\DB::table('project_circuit_calculations')
                    ->where('project_id', $projectId)
                    ->get();

                $instW10  = $calcRows10->sum('power_w');
                $demVa10  = $calcRows10->sum('demand_va_calculated') ?: $calcRows10->sum('power_va');
                $instKw10 = round($instW10 / 1000, 2);
                $demKva10 = round($demVa10 / 1000, 2);
                $numCirc10 = $calcRows10->count();
            @endphp

            {{-- Totais calculados --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-brand-yellow/10 border border-brand-yellow rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Circuitos</p>
                    <p class="text-2xl font-black text-brand-black">{{ $numCirc10 }}</p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Carga Instalada</p>
                    <p class="text-2xl font-black text-blue-800 dark:text-blue-300">{{ number_format($instKw10, 2, ',', '.') }}</p>
                    <p class="text-xs text-blue-500 font-medium">kW</p>
                </div>
                <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Demanda Provável</p>
                    <p class="text-2xl font-black text-purple-800 dark:text-purple-300">{{ number_format($demKva10, 2, ',', '.') }}</p>
                    <p class="text-xs text-purple-500 dark:text-purple-400 font-medium">kVA</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Corrente Total Est.</p>
                    @php
                        $vSE = $defaultVoltage ?: 127;
                        $iEst = $vSE > 0 ? round(($demVa10 / $vSE), 1) : 0;
                    @endphp
                    <p class="text-2xl font-black text-green-800 dark:text-green-300">{{ $iEst }}</p>
                    <p class="text-xs text-green-500 font-medium">A ({{ $vSE }}V)</p>
                </div>
            </div>

            {{-- Configurações do padrão --}}
            <div class="bg-gray-50 dark:bg-gray-900/40 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-4">Configurações do Padrão de Entrada</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Fases do Padrão</label>
                        <select wire:model="sePhases"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                            <option value="1">Monofásico</option>
                            <option value="2">Bifásico</option>
                            <option value="3">Trifásico</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Posição do Poste</label>
                        <input type="text" wire:model="sePolePosition" placeholder="Ex: Poste concreto 11m"
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                    </div>
                    <div class="sm:col-span-1"></div>
                    <div class="sm:col-span-3">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-300 mb-1">Observações do Padrão</label>
                        <textarea wire:model="seNotes" rows="3" placeholder="Anotações técnicas sobre o padrão de entrada..."
                            class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2.5 text-sm text-gray-800 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-brand-yellow resize-none"></textarea>
                    </div>
                </div>
            </div>

            {{-- Circuitos por tipo --}}
            @php
                $ilumCount = $calcRows10->where('circuit_type', 'ILUMINAÇÃO')->count();
                $tugCount  = $calcRows10->where('circuit_type', 'TUG')->count();
                $tueCount  = $calcRows10->where('circuit_type', 'TUE')->count();
            @endphp
            <div class="mt-5 grid grid-cols-3 gap-3 text-sm text-center">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 rounded-lg p-3">
                    <p class="text-xs text-blue-500 font-semibold mb-0.5">Iluminação</p>
                    <p class="text-xl font-black text-blue-800 dark:text-blue-300">{{ $ilumCount }}</p>
                    <p class="text-xs text-blue-400">circuitos</p>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-100 rounded-lg p-3">
                    <p class="text-xs text-green-500 font-semibold mb-0.5">TUG</p>
                    <p class="text-xl font-black text-green-800 dark:text-green-300">{{ $tugCount }}</p>
                    <p class="text-xs text-green-400">circuitos</p>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-100 dark:border-orange-800 rounded-lg p-3">
                    <p class="text-xs text-orange-500 font-semibold mb-0.5">TUE</p>
                    <p class="text-xl font-black text-orange-800 dark:text-orange-300">{{ $tueCount }}</p>
                    <p class="text-xs text-orange-400">circuitos</p>
                </div>
            </div>

            <div class="mt-5 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="font-semibold text-green-800 dark:text-green-300 text-sm">Dimensionamento concluído!</p>
                    <p class="text-green-700 dark:text-green-300 text-xs mt-0.5">Clique em "Concluir" para finalizar o projeto e salvar todas as informações.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- ══════════════════════════════════════════════════════════
             RODAPÉ DE NAVEGAÇÃO
        ══════════════════════════════════════════════════════════ --}}
        <div class="border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-6 py-4 flex items-center justify-between rounded-b-xl">
            <div>
                @if($currentStep > 1)
                <button wire:click="prevStep" wire:loading.attr="disabled" wire:target="prevStep"
                    class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500 text-gray-700 dark:text-gray-300 font-medium text-sm px-5 py-2.5 rounded-lg transition disabled:opacity-60">
                    <span wire:loading.remove wire:target="prevStep">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                    <span wire:loading wire:target="prevStep">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </span>
                    Anterior
                </button>
                @else
                    <div></div>
                @endif
            </div>

            <span class="text-sm text-gray-500 dark:text-gray-400 font-medium hidden sm:block">
                Step <span class="font-bold text-gray-700 dark:text-gray-300">{{ $currentStep }}</span> de {{ \App\Livewire\Student\Wizard\ProjectWizard::TOTAL_STEPS }}
            </span>

            <div>
                @if($currentStep < \App\Livewire\Student\Wizard\ProjectWizard::TOTAL_STEPS)
                @php $nextAction = $currentStep === 4 ? 'calculateCircuits' : 'nextStep'; @endphp
                <button wire:click="{{ $nextAction }}" wire:loading.attr="disabled" wire:target="{{ $nextAction }}"
                    class="inline-flex items-center gap-2 bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold text-sm px-5 py-2.5 rounded-lg transition disabled:opacity-60">
                    {{ $currentStep === 4 ? 'Calcular Circuitos' : 'Próximo' }}
                    <span wire:loading.remove wire:target="{{ $nextAction }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                    <span wire:loading wire:target="{{ $nextAction }}">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </span>
                </button>
                @else
                <button wire:click="finishProject" wire:loading.attr="disabled" wire:target="finishProject"
                    class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-bold text-sm px-5 py-2.5 rounded-lg transition disabled:opacity-60">
                    Concluir
                    <span wire:loading.remove wire:target="finishProject">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span wire:loading wire:target="finishProject">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </span>
                </button>
                @endif
            </div>
        </div>

    </div>{{-- fim card --}}

    {{-- ══════════════════════════════════════════════════════════
         MODAL DE CONFIGURAÇÃO DA DISTRIBUIÇÃO AUTOMÁTICA DE CIRCUITOS
    ══════════════════════════════════════════════════════════ --}}
    @if($showDistributeModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full overflow-hidden transform transition-all" @click.away="$wire.closeDistributeModal()">
            <div class="bg-brand-yellow px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-2 text-brand-black font-bold text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Configurar Distribuição Automática
                </div>
                <button wire:click="closeDistributeModal" class="text-brand-black/70 hover:text-brand-black transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                    O sistema agrupará as cargas respeitando a localização por andar e os limites de corrente, na seguinte sequência estrita: 
                    <span class="font-bold text-blue-600 dark:text-blue-400">1. Iluminação (C1...)</span> → 
                    <span class="font-bold text-green-600 dark:text-green-400">2. Tomadas TUG</span> → 
                    <span class="font-bold text-orange-600 dark:text-orange-400">3. TUEs</span>.
                </p>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1">
                        Número Mínimo de Circuitos de Iluminação
                    </label>
                    <input type="number" min="1" max="20" wire:model="autoDistributeMinLighting"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-center font-bold text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                    <p class="text-[11px] text-gray-400 mt-0.5">Quantidade mínima de circuitos que o sistema deve gerar para Iluminação.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-200 mb-1">
                        Número Mínimo de Circuitos de Tomadas TUG
                    </label>
                    <input type="number" min="1" max="30" wire:model="autoDistributeMinTug"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 text-sm text-center font-bold text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-brand-yellow">
                    <p class="text-[11px] text-gray-400 mt-0.5">Quantidade mínima de circuitos que o sistema deve gerar para Tomadas TUG.</p>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-900/50 px-6 py-3.5 flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                <button wire:click="closeDistributeModal" class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition">
                    Cancelar
                </button>
                <button wire:click="autoAssignCircuits"
                    wire:loading.attr="disabled"
                    wire:target="autoAssignCircuits"
                    class="bg-brand-yellow hover:bg-brand-yellow-hover text-brand-black font-bold text-xs px-5 py-2.5 rounded-lg transition shadow-sm flex items-center gap-2">
                    <span wire:loading.remove wire:target="autoAssignCircuits">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </span>
                    <span wire:loading wire:target="autoAssignCircuits">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </span>
                    Distribuir Circuitos
                </button>
            </div>
        </div>
    </div>
    @endif

</div>{{-- fim componente --}}
