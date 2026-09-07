<?php

declare(strict_types=1);

namespace App\Livewire\Student\Wizard;

use Livewire\Component;
use App\Models\Project;
use App\Models\ProjectSettings;
use App\Models\ProjectRoom;
use App\Models\ProjectInputRow;
use App\Services\Calculation\CalculationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectWizard extends Component
{
    public const TOTAL_STEPS = 8;
    public const STEP_LABELS = [
        1 => 'Dados do Projeto',
        2 => 'Cadastro dos Cômodos e Cargas Mínimas',
        3 => 'Levantamento das Cargas',
        4 => 'Atribuição de Circuitos',
        5 => 'Memorial de Cálculo e Dimensionamento',
        6 => 'Distribuição de Fases',
        7 => 'Eletrodutos / DPS / IDR',
        8 => 'Padrão de Entrada',
    ];

    public const ROOM_TYPES = [
        'SALA',
        'SALA DE JANTAR',
        'QUARTO',
        'SUÍTE',
        'DORMITÓRIO',
        'ESCRITÓRIO',
        'CLOSET',
        'COZINHA',
        'COPA',
        'COPA-COZINHA',
        'LAVANDERIA',
        'ÁREA DE SERVIÇO',
        'COZINHA-ÁREA DE SERVIÇO',
        'BANHEIRO',
        'LAVABO',
        'GARAGEM',
        'VARANDA',
        'TERRAÇO',
        'HALL',
        'CORREDOR',
        'ESCADA',
        'ÁREA EXTERNA',
        'SALA DE MÁQUINAS',
        'OUTRO',
    ];

    public const INSTALLATION_METHODS = ['A1', 'A2', 'B1', 'B2'];
    public const VOLTAGES  = [127, 220];
    public const PHASES    = [1, 2, 3];

    // ─── Estado ───────────────────────────────────────────
    public ?int $projectId = null;
    public int  $currentStep = 1;

    // Step 1
    public string $name          = '';
    public string $clientName    = '';
    public string $clientPhone   = '';
    public string $clientEmail   = '';
    public string $address       = '';
    public string $city          = '';
    public string $state         = '';
    public string $observations  = '';
    public int    $floorsCount   = 1;

    // Step 4 — auto distribution modal
    public bool $showDistributeModal          = false;
    public int  $autoDistributeMinLighting    = 1;
    public int  $autoDistributeMinTug         = 1;

    // Step 2 — project defaults
    public int    $defaultVoltage             = 127;
    public int    $defaultPhases              = 1;
    public float  $defaultPowerFactor         = 1.0;
    public string $defaultInstallationMethod  = 'B1';
    public int    $defaultTemperatureC        = 30;
    public float  $defaultVoltageDropPercent  = 4.0;

    // Step 2 — rooms
    public array $rooms = [];

    // Step 2 — editor de planta baixa (opcional, alimenta $rooms via importRoomsFromFloorPlan)
    public bool $showFloorPlanEditor = false;

    // Step 3 — loads
    public array $loads = [];

    // Undo history — stores up to 5 snapshots of $loads
    public array $history = [];

    // Step 7 — manual overrides per circuit
    public array $circuitOverrides = [];

    // Step 8 — phase assignment per circuit ('r'|'s'|'t')
    public array $phaseAssignments = [];

    // Step 9 — DPS / IDR inputs
    public string $dpsLocationType = 'Descargas Indiretas';
    public int    $dpsVoltage      = 127;
    public float  $shortCircuitKa  = 5.0;
    public string $hasNeutral      = 'SIM';

    // Step 10 — service entrance inputs
    public int    $sePhases       = 1;
    public string $sePolePosition = '';
    public string $seNotes        = '';

    // Feedback
    public string $successMessage = '';
    public string $errorMessage   = '';

    // ─── Lifecycle ───────────────────────────────────────

    public function hydrate(): void
    {
        $this->normalizeRooms();
        $this->normalizeLoads();
    }

    private function normalizeRooms(): void
    {
        foreach ($this->rooms as &$room) {
            // Step 2 — min load fields
            $room['floor_number']                ??= 1;
            $room['lighting_va_calculated']      ??= null;
            $room['lighting_rule_description']   ??= '';
            $room['tug_rule_group']              ??= '';
            $room['tug_qty_calculated']          ??= null;
            $room['tug_va_calculated']           ??= null;
            $room['tug_rule_description']        ??= '';
            $room['total_minimum_va_calculated'] ??= null;
            $room['total_minimum_va_final']      ??= null;
            $room['use_manual_lighting']         ??= false;
            $room['lighting_va_manual']          ??= '';
            $room['use_manual_tug_qty']          ??= false;
            $room['tug_qty_manual']              ??= '';
            $room['tug_qty_600_calculated']      ??= null;
            $room['tug_qty_100_calculated']      ??= null;
            $room['tug_qty_600_manual']          ??= '';
            $room['tug_qty_100_manual']          ??= '';
            $room['use_manual_tug_va']           ??= false;
            $room['tug_va_manual']               ??= '';
            // Legacy step 3 compat
            $room['suggestion_applied']          ??= false;
            $room['lighting_below_min']          ??= false;
            $room['tug_below_min']               ??= false;
            $room['has_lighting']                ??= false;
            $room['has_tug']                     ??= false;
            $room['min_lighting_va']             ??= null;
            $room['min_tug_qty']                 ??= null;
            $room['tug_unit_va']                 ??= 100;
            $room['tug_unit_va_min']             ??= 100;
            $room['tues']                        ??= [];
        }
        unset($room);
    }

    private function formatTugDescription(array $room, int $fallbackQty = 0, int $fallbackVa = 0): string
    {
        $qty600 = 0;
        $qty100 = 0;
        if (!empty($room['use_manual_tug_qty'])) {
            $qty600 = (($room['tug_qty_600_manual'] ?? '') !== '') ? (int)$room['tug_qty_600_manual'] : (int)($room['tug_qty_600_calculated'] ?? 0);
            $qty100 = (($room['tug_qty_100_manual'] ?? '') !== '') ? (int)$room['tug_qty_100_manual'] : (int)($room['tug_qty_100_calculated'] ?? 0);
        } elseif (($room['tug_va_calculated'] ?? null) !== null) {
            $qty600 = (int)($room['tug_qty_600_calculated'] ?? 0);
            $qty100 = (int)($room['tug_qty_100_calculated'] ?? 0);
        } else {
            $type  = (string)($room['room_type'] ?? '');
            $area  = (float)($room['area_m2'] ?? 0);
            $perim = (float)($room['perimeter_m'] ?? 0);
            if ($perim > 0 || $area > 0) {
                $calc   = $this->calcMinTugData($type, $area, $perim);
                $qty600 = $calc['qty_600'] ?? 0;
                $qty100 = $calc['qty_100'] ?? 0;
            }
        }

        $parts = [];
        if ($qty600 > 0) $parts[] = "{$qty600}× 600VA";
        if ($qty100 > 0) $parts[] = "{$qty100}× 100VA";

        if (empty($parts) && $fallbackQty > 0) {
            $unitVa  = $fallbackQty > 0 ? (int)round($fallbackVa / $fallbackQty) : 0;
            $parts[] = "{$fallbackQty}× {$unitVa}VA";
        }

        return "TUG (" . (empty($parts) ? "100VA" : implode(' + ', $parts)) . ")";
    }

    private function normalizeLoads(): void
    {
        foreach ($this->loads as &$load) {
            $load['id']                   ??= null;
            $load['room_id']              ??= null;
            $load['room_index']           ??= 0;
            $load['room_label']           ??= '';
            $load['load_type']            ??= 'ILUMINAÇÃO';
            $load['description']          ??= '';
            $load['quantity']             ??= 1;
            $load['power_va']             ??= 0;
            $load['unit_va']              ??= 0;
            $load['is_auto']              ??= false;
            $load['sort_order']           ??= 0;
            $load['phases']               ??= 1;
            $load['voltage_v']            ??= 127;
            $load['installation_method']  ??= 'B1';
            $load['temperature_c']        ??= 30;
            $load['grouped_circuits']     ??= 1;
            $load['fp']                   ??= 1.0;
            $load['power_w']              ??= null;
            $load['current_a']            ??= null;
            $load['grouping_factor']      ??= null;
            $load['temperature_factor']   ??= null;
            $load['corrected_current_a']  ??= null;
            $load['use_manual_va']              ??= false;
            $load['manual_va']                ??= '';
            $load['circuit_number']           ??= null;
            $load['split_original_va']        ??= null;
            $load['split_original_description'] ??= null;

            if (($load['load_type'] ?? '') === 'TUG' && (bool)($load['is_auto'] ?? false)) {
                $room = $this->rooms[$load['room_index'] ?? 0] ?? [];
                if (empty($load['description']) || str_contains($load['description'], '400VA') || str_contains($load['description'], '350VA') || preg_match('/^TUG\s*\(\d+[\s×]*\d+VA\)$/i', $load['description'])) {
                    $load['description'] = $this->formatTugDescription($room, (int)($load['quantity'] ?? 0), (int)($load['power_va'] ?? 0));
                }
            }
        }
        unset($load);
    }

    // ─── Mount ───────────────────────────────────────────

    public function mount(?int $projectId = null): void
    {
        $this->projectId = $projectId;

        if ($projectId) {
            $project = Project::where('id', $projectId)
                ->where('user_id', Auth::id())
                ->with(['settings', 'rooms' => fn($q) => $q->orderBy('sort_order')])
                ->firstOrFail();

            $this->name         = $project->name;
            $this->clientName   = $project->client_name  ?? '';
            $this->clientPhone  = $project->client_phone ?? '';
            $this->clientEmail  = $project->client_email ?? '';
            $this->address      = $project->address      ?? '';
            $this->city         = $project->city         ?? '';
            $this->state        = $project->state        ?? '';
            $this->observations = $project->observations ?? '';
            $this->floorsCount  = (int)($project->floors_count ?? 1);

            if ($project->settings) {
                $s = $project->settings;
                $this->defaultVoltage            = $s->default_voltage             ?? 127;
                $this->defaultPhases             = $s->default_phases              ?? 1;
                $this->defaultPowerFactor        = (float)($s->default_power_factor        ?? 1.0);
                $this->defaultInstallationMethod = $s->default_installation_method ?? 'B1';
                $this->defaultTemperatureC       = $s->default_temperature_c       ?? 30;
                $this->defaultVoltageDropPercent = (float)($s->default_voltage_drop_percent ?? 4.0);
            }

            foreach ($project->rooms as $room) {
                $lighting = $room->inputRows()->where('load_type', 'ILUMINAÇÃO')->first();
                $tug      = $room->inputRows()->where('load_type', 'TUG')->first();
                $tues     = $room->inputRows()->where('load_type', 'TUE')->get();

                $minLighting = $this->calcMinLightingVa((float)($room->area_m2 ?? 0));
                $tugData     = $this->calcMinTugData($room->room_type, (float)($room->area_m2 ?? 0), (float)($room->perimeter_m ?? 0));

                $this->rooms[] = [
                    'id'           => $room->id,
                    'room_type'    => $room->room_type,
                    'description'  => $room->description ?? '',
                    'area_m2'      => $room->area_m2     ?? '',
                    'perimeter_m'  => $room->perimeter_m ?? '',
                    'sort_order'   => $room->sort_order,
                    'floor_number' => (int)($room->floor_number ?? 1),

                    // Step 2 — min load fields (from DB)
                    'lighting_va_calculated'     => $room->lighting_va_calculated,
                    'lighting_va_manual'         => $room->lighting_va_manual !== null ? (string)$room->lighting_va_manual : '',
                    'use_manual_lighting'        => (bool)($room->use_manual_lighting ?? false),
                    'lighting_rule_description'  => $room->lighting_rule_description ?? '',
                    'tug_rule_group'             => $room->tug_rule_group ?? '',
                    'tug_qty_calculated'         => $room->tug_qty_calculated,
                    'tug_qty_600_calculated'     => $tugData['qty_600'] ?? 0,
                    'tug_qty_100_calculated'     => $tugData['qty_100'] ?? 0,
                    'tug_qty_manual'             => $room->tug_qty_manual !== null ? (string)$room->tug_qty_manual : '',
                    'tug_qty_600_manual'         => $room->tug_qty_600_manual !== null ? (string)$room->tug_qty_600_manual : '',
                    'tug_qty_100_manual'         => $room->tug_qty_100_manual !== null ? (string)$room->tug_qty_100_manual : '',
                    'use_manual_tug_qty'         => (bool)($room->use_manual_tug_qty ?? false),
                    'tug_va_calculated'          => $room->tug_va_calculated,
                    'tug_va_manual'              => $room->tug_va_manual !== null ? (string)$room->tug_va_manual : '',
                    'use_manual_tug_va'          => (bool)($room->use_manual_tug_va ?? false),
                    'tug_rule_description'       => $room->tug_rule_description ?? '',
                    'total_minimum_va_calculated'=> $room->total_minimum_va_calculated,
                    'total_minimum_va_final'     => $room->total_minimum_va_final,

                    // Legacy step 3 compat
                    'suggestion_applied'   => false,
                    'has_lighting'         => $lighting !== null,
                    'lighting_va'          => $lighting ? (string)$lighting->specific_power_va : '',
                    'lighting_row_id'      => $lighting?->id,
                    'min_lighting_va'      => $minLighting,
                    'lighting_below_min'   => $lighting ? ((float)$lighting->specific_power_va < $minLighting) : false,

                    'has_tug'              => $tug !== null,
                    'tug_qty'              => $tug ? (string)$tug->quantity : '',
                    'tug_unit_va'          => $tug ? (int)($tug->specific_power_va / max(1, (int)$tug->quantity)) : ($tugData['unit_va'] ?? 100),
                    'tug_row_id'           => $tug?->id,
                    'min_tug_qty'          => $tugData['qty'] ?? 0,
                    'tug_unit_va_min'      => $tugData['unit_va'] ?? 100,
                    'tug_below_min'        => $tug ? ((int)$tug->quantity < ($tugData['qty'] ?? 0)) : false,

                    'phases'               => $lighting?->phases ?? $tug?->phases ?? '',
                    'voltage'              => $lighting?->voltage ?? $tug?->voltage ?? '',
                    'installation_method'  => $lighting?->installation_method ?? $tug?->installation_method ?? '',
                    'temperature_c'        => $lighting?->temperature_c ?? $tug?->temperature_c ?? '',
                    'distance_m'           => $lighting?->distance_m ?? $tug?->distance_m ?? '',
                    'voltage_drop_percent' => $lighting?->voltage_drop_percent ?? $tug?->voltage_drop_percent ?? '',
                    'grouped_circuits'     => $lighting?->grouped_circuits ?? $tug?->grouped_circuits ?? '',

                    'tues' => $tues->map(fn($t) => [
                        'id'          => $t->id,
                        'description' => $t->tue_description ?? $t->description ?? '',
                        'power_va'    => (string)$t->specific_power_va,
                        'phases'      => (string)($t->phases ?? ''),
                        'voltage'     => (string)($t->voltage ?? ''),
                        'distance_m'  => (string)($t->distance_m ?? ''),
                    ])->toArray(),
                ];
            }

            $this->currentStep = min($project->progress_step ?? 1, self::TOTAL_STEPS);

            if ($this->currentStep >= 3) {
                $this->loadLoadsFromDb();
                if ($this->hasRoomsMissingFromLoads()) {
                    $this->generateLoadsFromRooms();
                }
            }

            if ($this->currentStep >= 5) {
                $this->loadCircuitOverrides();
                $this->loadPhaseAssignments();
                $this->loadServiceEntrance();
            }
        }

        $this->normalizeRooms();
        $this->normalizeLoads();

        if (empty($this->rooms)) {
            $this->addRoom();
        }
    }

    // ─── Navegação ────────────────────────────────────────

    public function nextStep(): void
    {
        $this->clearMessages();
        $this->validateCurrentStep();
        $this->saveCurrentStep();
        $this->currentStep = min($this->currentStep + 1, self::TOTAL_STEPS);
        $this->updateProgress();

        if ($this->currentStep === 3 || $this->currentStep === 4) {
            if (empty($this->loads)) {
                $this->loadLoadsFromDb();
            }
            if (empty($this->loads) || $this->hasRoomsMissingFromLoads()) {
                $this->generateLoadsFromRooms();
            }
        }

        if ($this->currentStep >= 5 && empty($this->circuitOverrides)) {
            $this->loadCircuitOverrides();
            $this->loadPhaseAssignments();
            $this->loadServiceEntrance();
        }
    }

    // Detecta cômodos (com id salvo) que ainda não têm nenhuma carga em $this->loads -
    // acontece quando o usuário volta ao Step 2 e adiciona cômodos depois de já ter
    // visitado o Step 3, já que a geração automática só rodava na primeira entrada.
    private function hasRoomsMissingFromLoads(): bool
    {
        $loadRoomIds = array_unique(array_filter(array_map(
            fn($l) => $l['room_id'] ?? null,
            $this->loads
        )));

        foreach ($this->rooms as $room) {
            $roomId = $room['id'] ?? null;
            if ($roomId && !in_array($roomId, $loadRoomIds)) {
                return true;
            }
        }

        return false;
    }

    public function prevStep(): void
    {
        $this->clearMessages();
        $this->saveCurrentStep();
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    // Chamado pelo botão "Concluir" do último passo: salva e marca o projeto como concluído,
    // liberando o link de download do PDF na listagem de projetos.
    public function finishProject(): void
    {
        $this->clearMessages();
        $this->saveCurrentStep();

        if ($this->projectId) {
            Project::where('id', $this->projectId)->update([
                'status'           => 'completed',
                'progress_step'    => self::TOTAL_STEPS,
                'progress_percent' => 100,
            ]);
        }

        $this->successMessage = 'Projeto concluído! Acesse "Meus Projetos" para baixar o PDF.';
    }

    public function goToStep(int $step): void
    {
        if ($step >= 1 && $step <= self::TOTAL_STEPS && $step <= $this->getMaxAllowedStep()) {
            $this->clearMessages();
            $this->currentStep = $step;

            if ($step === 3 || $step === 4) {
                if (empty($this->loads)) {
                    $this->loadLoadsFromDb();
                }
                if (empty($this->loads) || $this->hasRoomsMissingFromLoads()) {
                    $this->generateLoadsFromRooms();
                }
            }

            if ($step >= 5 && empty($this->circuitOverrides)) {
                $this->loadCircuitOverrides();
                $this->loadPhaseAssignments();
                $this->loadServiceEntrance();
            }
        }
    }

    private function getMaxAllowedStep(): int
    {
        if (!$this->projectId) return 1;
        $project = Project::find($this->projectId);
        return min(($project?->progress_step ?? 1) + 1, self::TOTAL_STEPS);
    }

    // ─── Step 4: Calcular Circuitos ────────────

    public function calculateCircuits(): void
    {
        $this->clearMessages();
        if (!$this->projectId) {
            $this->errorMessage = 'Salve o projeto antes de calcular.';
            return;
        }

        $this->validateCurrentStep();
        $this->saveCurrentStep();

        try {
            $service = app(CalculationService::class);
            $result  = $service->calculateProject($this->projectId);

            if ($result['success']) {
                $this->successMessage = 'Circuitos calculados com sucesso!';
                $this->currentStep = min($this->currentStep + 1, self::TOTAL_STEPS);
                $this->updateProgress();
                $this->circuitOverrides = [];
                $this->phaseAssignments = [];
                $this->loadCircuitOverrides();
                $this->loadPhaseAssignments();
                $this->loadServiceEntrance();
            } else {
                $this->errorMessage = implode(' ', $result['messages']);
            }
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erro ao calcular: ' . $e->getMessage();
        }
    }

    // ─── Validação ────────────────────────────────────────

    private function validateCurrentStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate([
                'name'        => 'required|min:3|max:180',
                'clientEmail' => 'nullable|email|max:180',
                'clientPhone' => 'nullable|max:30',
            ], [
                'name.required'     => 'O nome do projeto é obrigatório.',
                'name.min'          => 'O nome deve ter pelo menos 3 caracteres.',
                'clientEmail.email' => 'E-mail do cliente inválido.',
            ]),
            2 => $this->validateRoomsStep2(),
            3 => $this->validateLoads(),
            4 => $this->validateStep4(),
            default => null,
        };
    }

    private function validateRoomsStep2(): void
    {
        if (empty($this->rooms)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'rooms' => 'Adicione pelo menos um cômodo.',
            ]);
        }
        $errors = [];
        foreach ($this->rooms as $i => $room) {
            if (empty($room['room_type'])) {
                $errors["rooms.{$i}.room_type"] = 'Selecione o tipo de cômodo.';
            }
            if (empty($room['description'])) {
                $errors["rooms.{$i}.description"] = 'Informe a descrição do cômodo.';
            }
            if (!isset($room['area_m2']) || $room['area_m2'] === '' || (float)$room['area_m2'] <= 0) {
                $errors["rooms.{$i}.area_m2"] = 'Informe a área do cômodo (maior que zero).';
            }
            if (!isset($room['perimeter_m']) || $room['perimeter_m'] === '' || (float)$room['perimeter_m'] <= 0) {
                $errors["rooms.{$i}.perimeter_m"] = 'Informe o perímetro do cômodo (maior que zero).';
            }
        }
        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function validateRooms(): void
    {
        if (empty($this->rooms)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'rooms' => 'Adicione pelo menos um cômodo.',
            ]);
        }
        $errors = [];
        foreach ($this->rooms as $i => $room) {
            if (empty($room['room_type'])) {
                $errors["rooms.{$i}.room_type"] = 'Selecione o tipo de cômodo.';
            }
        }
        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    private function validateLoads(): void
    {
        $errors = [];
        foreach ($this->loads as $li => $load) {
            if (($load['load_type'] ?? '') === 'TUE') {
                if (empty($load['description'])) {
                    $errors["loads.{$li}.description"] = 'Informe o nome do equipamento.';
                }
                if (empty($load['power_va']) || (int)$load['power_va'] <= 0) {
                    $errors["loads.{$li}.power_va"] = 'Informe a potência (VA) do equipamento.';
                }
            }
        }
        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    // ─── Persistência ────────────────────────────────────

    private function saveCurrentStep(): void
    {
        DB::transaction(function () {
            match ($this->currentStep) {
                1 => $this->saveStep1(),
                2 => $this->saveStep2(),
                3 => $this->saveStep3(),
                4 => $this->saveStep4(),
                5 => $this->saveStep5(),
                6 => $this->saveStep6(),
                7 => $this->saveStep7(),
                8 => $this->saveStep8(),
                default => null,
            };
        });
    }

    public function saveStep1(): void
    {
        $data = [
            'user_id'      => Auth::id(),
            'name'         => $this->name,
            'client_name'  => $this->clientName  ?: null,
            'client_phone' => $this->clientPhone ?: null,
            'client_email' => $this->clientEmail ?: null,
            'address'      => $this->address     ?: null,
            'city'         => $this->city        ?: null,
            'state'        => $this->state       ?: null,
            'observations' => $this->observations ?: null,
            'floors_count' => max(1, (int)$this->floorsCount),
            'status'       => 'in_progress',
        ];

        if ($this->projectId) {
            Project::where('id', $this->projectId)->where('user_id', Auth::id())->update($data);
        } else {
            $project = Project::create($data);
            $this->projectId = $project->id;
        }
    }

    public function saveStep2(): void
    {
        if (!$this->projectId) return;

        $savedIds = [];
        foreach ($this->rooms as $i => &$room) {
            $model = ProjectRoom::updateOrCreate(
                ['id' => $room['id'] ?? null, 'project_id' => $this->projectId],
                [
                    'project_id'   => $this->projectId,
                    'room_type'    => $room['room_type'],
                    'description'  => $room['description'] ?: null,
                    'area_m2'      => $room['area_m2']    !== '' ? (float)$room['area_m2']    : null,
                    'perimeter_m'  => $room['perimeter_m'] !== '' ? (float)$room['perimeter_m'] : null,
                    'sort_order'   => $i,
                    'floor_number' => max(1, (int)($room['floor_number'] ?? 1)),

                    'lighting_va_calculated'     => $room['lighting_va_calculated'],
                    'lighting_va_manual'         => ($room['use_manual_lighting'] && $room['lighting_va_manual'] !== '') ? (int)$room['lighting_va_manual'] : null,
                    'use_manual_lighting'        => $room['use_manual_lighting'] ? 1 : 0,
                    'lighting_rule_description'  => $room['lighting_rule_description'] ?: null,

                    'tug_rule_group'             => $room['tug_rule_group'] ?: null,
                    'tug_qty_calculated'         => $room['tug_qty_calculated'],
                    'tug_qty_manual'             => ($room['use_manual_tug_qty'] && ($room['tug_qty_manual'] ?? '') !== '') ? (int)$room['tug_qty_manual'] : null,
                    'tug_qty_600_manual'         => ($room['use_manual_tug_qty'] && ($room['tug_qty_600_manual'] ?? '') !== '') ? (int)$room['tug_qty_600_manual'] : null,
                    'tug_qty_100_manual'         => ($room['use_manual_tug_qty'] && ($room['tug_qty_100_manual'] ?? '') !== '') ? (int)$room['tug_qty_100_manual'] : null,
                    'use_manual_tug_qty'         => $room['use_manual_tug_qty'] ? 1 : 0,
                    'tug_va_calculated'          => $room['tug_va_calculated'],
                    'tug_va_manual'              => null,
                    'use_manual_tug_va'          => 0,
                    'tug_rule_description'       => $room['tug_rule_description'] ?: null,

                    'total_minimum_va_calculated'=> $room['total_minimum_va_calculated'],
                    'total_minimum_va_final'     => $room['total_minimum_va_final'],
                ]
            );
            $room['id'] = $model->id;
            $savedIds[] = $model->id;
        }
        unset($room);

        $deleted = ProjectRoom::where('project_id', $this->projectId)
            ->whereNotIn('id', $savedIds)
            ->get();
        foreach ($deleted as $dr) {
            ProjectInputRow::where('room_id', $dr->id)->delete();
            try { DB::table('project_loads')->where('room_id', $dr->id)->delete(); } catch (\Throwable) {}
            $dr->delete();
        }
    }

    public function saveStep3(): void
    {
        if (!$this->projectId) return;

        // Save to project_loads
        $savedLoadIds = [];
        foreach ($this->loads as $li => &$load) {
            if (empty($load['room_id'])) continue;

            $data = [
                'project_id'          => $this->projectId,
                'room_id'             => (int)$load['room_id'],
                'load_type'           => $load['load_type'],
                'description'         => ($load['description'] ?? '') ?: null,
                'quantity'            => (int)($load['quantity'] ?? 1),
                'power_va'            => (int)($load['power_va'] ?? 0),
                'unit_va'             => (int)($load['unit_va'] ?? 0),
                'is_auto'             => ($load['is_auto'] ?? false) ? 1 : 0,
                'sort_order'          => (int)($load['sort_order'] ?? $li),
                'phases'              => (int)($load['phases'] ?? 1),
                'voltage_v'           => (int)($load['voltage_v'] ?? 127),
                'installation_method' => $load['installation_method'] ?? 'B1',
                'temperature_c'       => (int)($load['temperature_c'] ?? 30),
                'grouped_circuits'    => (int)($load['grouped_circuits'] ?? 1),
                'fp'                  => (float)($load['fp'] ?? 1.0),
                'power_w'             => ($load['power_w'] ?? null) !== null ? (float)$load['power_w'] : null,
                'current_a'           => ($load['current_a'] ?? null) !== null ? (float)$load['current_a'] : null,
                'grouping_factor'     => ($load['grouping_factor'] ?? null) !== null ? (float)$load['grouping_factor'] : null,
                'temperature_factor'  => ($load['temperature_factor'] ?? null) !== null ? (float)$load['temperature_factor'] : null,
                'corrected_current_a' => ($load['corrected_current_a'] ?? null) !== null ? (float)$load['corrected_current_a'] : null,
                'use_manual_va'              => ($load['use_manual_va'] ?? false) ? 1 : 0,
                'manual_va'                  => (($load['use_manual_va'] ?? false) && ($load['manual_va'] ?? '') !== '') ? (int)$load['manual_va'] : null,
                'split_original_va'          => ($load['split_original_va'] ?? null) !== null ? (int)$load['split_original_va'] : null,
                'split_original_description' => ($load['split_original_description'] ?? null) !== null ? (string)$load['split_original_description'] : null,
            ];

            try {
                if (!empty($load['id'])) {
                    DB::table('project_loads')->where('id', $load['id'])->update($data + ['updated_at' => now()]);
                    $savedLoadIds[] = (int)$load['id'];
                } else {
                    $id = DB::table('project_loads')->insertGetId($data + ['created_at' => now(), 'updated_at' => now()]);
                    $load['id'] = $id;
                    $savedLoadIds[] = $id;
                }
            } catch (\Throwable) {}
        }
        unset($load);

        // Remove deleted loads
        try {
            $q = DB::table('project_loads')->where('project_id', $this->projectId);
            if (!empty($savedLoadIds)) {
                $q->whereNotIn('id', $savedLoadIds);
            }
            $q->delete();
        } catch (\Throwable) {}

        // Mirror to project_input_rows for Step 4+ compatibility
        $this->mirrorLoadsToInputRows();
    }

    private function mirrorLoadsToInputRows(): void
    {
        if (!$this->projectId) return;

        // Clear existing input rows for this project to prevent orphan rows
        ProjectInputRow::where('project_id', $this->projectId)->delete();

        $sequentialCircuit = 1;
        $savedRoomIds      = [];

        // Detect if circuit assignments exist (Step 4 ran)
        $hasCircuitAssignments = !empty(array_filter(
            $this->loads,
            fn($l) => ($l['circuit_number'] ?? null) !== null && (int)($l['circuit_number'] ?? 0) > 0
        ));

        // Index loads by room_id for fast lookup
        $loadsByRoom = [];
        foreach ($this->loads as $load) {
            $rid = $load['room_id'] ?? null;
            if ($rid) $loadsByRoom[(int)$rid][] = $load;
        }

        foreach ($this->rooms as $room) {
            $roomId = $room['id'] ?? null;
            if (!$roomId) continue;
            $savedRoomIds[] = $roomId;

            foreach ($loadsByRoom[(int)$roomId] ?? [] as $load) {
                $effectiveVa = ($load['use_manual_va'] ?? false) && ($load['manual_va'] ?? '') !== ''
                    ? (int)$load['manual_va']
                    : (int)($load['power_va'] ?? 0);
                if ($effectiveVa <= 0) continue;

                $assignedCircuit = ($hasCircuitAssignments && ($load['circuit_number'] ?? null) !== null && (int)($load['circuit_number'] ?? 0) > 0)
                    ? (int)$load['circuit_number']
                    : $sequentialCircuit;
                if (!$hasCircuitAssignments) $sequentialCircuit++;

                $rowData = [
                    'project_id'          => $this->projectId,
                    'room_id'             => $roomId,
                    'circuit_number'      => $assignedCircuit,
                    'room_type'           => $room['room_type'],
                    'description'         => ($load['load_type'] === 'TUE' ? ($load['description'] ?? null) : ($room['description'] ?? null)) ?: null,
                    'load_type'           => $load['load_type'],
                    'specific_power_va'   => $effectiveVa,
                    'quantity'            => (int)($load['quantity'] ?? 1),
                    'phases'              => (int)($load['phases'] ?? $this->defaultPhases),
                    'voltage'             => (int)($load['voltage_v'] ?? $this->defaultVoltage),
                    'installation_method' => $load['installation_method'] ?? $this->defaultInstallationMethod,
                    'temperature_c'       => (int)($load['temperature_c'] ?? $this->defaultTemperatureC),
                    'distance_m'          => null,
                    'voltage_drop_percent'=> $this->defaultVoltageDropPercent,
                    'grouped_circuits'    => (int)($load['grouped_circuits'] ?? 1),
                    'power_factor'        => (float)($load['fp'] ?? 1.0),
                    'is_below_minimum'    => 0,
                    'minimum_va'          => null,
                    'area_m2'             => $room['area_m2'] !== '' ? (float)$room['area_m2'] : null,
                    'perimeter_m'         => $room['perimeter_m'] !== '' ? (float)$room['perimeter_m'] : null,
                    'sort_order'          => (int)($load['sort_order'] ?? 0),
                    'tue_description'     => $load['load_type'] === 'TUE' ? (($load['description'] ?? null) ?: null) : null,
                ];

                ProjectInputRow::create($rowData);
            }
        }

        // Remove rooms no longer in list
        $deletedRooms = ProjectRoom::where('project_id', $this->projectId)
            ->whereNotIn('id', $savedRoomIds)
            ->get();
        foreach ($deletedRooms as $dr) {
            ProjectInputRow::where('room_id', $dr->id)->delete();
            $dr->delete();
        }
    }

    public function updateCircuitParam(int $circuitNumber, string $field, mixed $value): void
    {
        if (!isset($this->circuitOverrides[$circuitNumber])) {
            $this->circuitOverrides[$circuitNumber] = [
                'distance_m'           => 0.0,
                'installation_method'  => 'B1',
                'temperature_c'        => 30,
                'grouped_circuits'     => 1,
                'voltage_drop_percent' => 4.0,
                'use_manual_conductor' => false,
                'manual_conductor_mm2' => '',
                'use_manual_breaker'   => false,
                'manual_breaker_a'     => '',
            ];
        }

        $this->circuitOverrides[$circuitNumber][$field] = $value;
        $this->saveStep5();
    }

    public function saveStep5(): void
    {
        if (!$this->projectId) return;

        foreach ($this->circuitOverrides as $cn => $override) {
            $distance   = isset($override['distance_m']) && $override['distance_m'] !== '' ? (float)$override['distance_m'] : 0.0;
            $instMethod = !empty($override['installation_method']) ? (string)$override['installation_method'] : 'B1';
            $tempC      = isset($override['temperature_c']) && $override['temperature_c'] !== '' ? (int)$override['temperature_c'] : 30;
            $grouped    = isset($override['grouped_circuits']) && $override['grouped_circuits'] !== '' ? max(1, (int)$override['grouped_circuits']) : 1;
            $vDrop      = isset($override['voltage_drop_percent']) && $override['voltage_drop_percent'] !== '' ? (float)$override['voltage_drop_percent'] : 4.0;
            $useCond    = !empty($override['use_manual_conductor']) ? 1 : 0;
            $manCond    = (!empty($override['use_manual_conductor']) && ($override['manual_conductor_mm2'] ?? '') !== '') ? (float)$override['manual_conductor_mm2'] : null;
            $useBrk     = !empty($override['use_manual_breaker']) ? 1 : 0;
            $manBrk     = (!empty($override['use_manual_breaker']) && ($override['manual_breaker_a'] ?? '') !== '') ? (int)$override['manual_breaker_a'] : null;

            try {
                DB::table('project_input_rows')
                    ->where('project_id', $this->projectId)
                    ->where('circuit_number', (int) $cn)
                    ->update([
                        'distance_m'           => $distance,
                        'installation_method'  => $instMethod,
                        'temperature_c'        => $tempC,
                        'grouped_circuits'     => $grouped,
                        'voltage_drop_percent' => $vDrop,
                        'updated_at'           => now(),
                    ]);

                DB::table('project_circuit_calculations')
                    ->where('project_id', $this->projectId)
                    ->where('circuit_number', (int) $cn)
                    ->update([
                        'distance_m'                 => $distance,
                        'installation_method'        => $instMethod,
                        'temperature_c'              => $tempC,
                        'grouped_circuits'           => $grouped,
                        'voltage_drop_percent'       => $vDrop,
                        'use_manual_final_conductor' => $useCond,
                        'final_conductor_manual_mm2' => $manCond,
                        'use_manual_breaker'         => $useBrk,
                        'breaker_manual_a'           => $manBrk,
                        'updated_at'                 => now(),
                    ]);
            } catch (\Throwable) {}
        }

        try {
            $service = app(CalculationService::class);
            $service->calculateProject($this->projectId);
        } catch (\Throwable) {}
    }

    public function saveStep6(): void
    {
        if (!$this->projectId) return;

        $circuits = DB::table('project_circuit_calculations')
            ->where('project_id', $this->projectId)
            ->orderBy('circuit_number')
            ->get();

        foreach ($circuits as $circuit) {
            $circNum = (int) $circuit->circuit_number;
            $phases  = (int) ($circuit->phases ?? 1);
            $powerVa = (float) ($circuit->power_va ?? 0);
            $phase   = $this->phaseAssignments[$circNum] ?? 'r';

            if ($phases === 3) {
                $useR = $useS = $useT = true;
                $loadR = $loadS = $loadT = $powerVa / 3;
            } else {
                $useR  = $phase === 'r';
                $useS  = $phase === 's';
                $useT  = $phase === 't';
                $loadR = $useR ? $powerVa : 0;
                $loadS = $useS ? $powerVa : 0;
                $loadT = $useT ? $powerVa : 0;
            }

            try {
                $existing = DB::table('project_phase_distribution')
                    ->where('project_id', $this->projectId)
                    ->where('circuit_number', $circNum)
                    ->first();

                $data = [
                    'project_id'     => $this->projectId,
                    'circuit_number' => $circNum,
                    'use_phase_r'    => $useR ? 1 : 0,
                    'use_phase_s'    => $useS ? 1 : 0,
                    'use_phase_t'    => $useT ? 1 : 0,
                    'load_r_va'      => $loadR,
                    'load_s_va'      => $loadS,
                    'load_t_va'      => $loadT,
                    'updated_at'     => now(),
                ];

                if ($existing) {
                    DB::table('project_phase_distribution')
                        ->where('id', $existing->id)
                        ->update($data);
                } else {
                    $data['created_at'] = now();
                    DB::table('project_phase_distribution')->insert($data);
                }
            } catch (\Throwable) {}
        }
    }

    public function saveStep7(): void
    {
        if (!$this->projectId) return;
        try {
            $this->upsertServiceEntrance([
                'short_circuit_ka'  => $this->shortCircuitKa,
                'has_neutral'       => $this->hasNeutral,
                'dps_location_type' => $this->dpsLocationType,
                'dps_voltage'       => $this->dpsVoltage,
            ]);
        } catch (\Throwable) {}
    }

    public function saveStep8(): void
    {
        if (!$this->projectId) return;

        try {
            $circuits = DB::table('project_circuit_calculations')
                ->where('project_id', $this->projectId)
                ->get();

            $installedW = 0.0;
            $demandVa   = 0.0;
            foreach ($circuits as $c) {
                $installedW += (float) ($c->power_w ?? 0);
                $demandVa   += (float) ($c->demand_va_calculated ?? $c->power_va ?? 0);
            }

            $this->upsertServiceEntrance([
                'installed_load_kw'   => round($installedW / 1000, 4),
                'probable_demand_kva' => round($demandVa / 1000, 4),
                'phases'              => $this->sePhases,
                'pole_position'       => $this->sePolePosition ?: null,
                'notes'               => $this->seNotes ?: null,
            ]);
        } catch (\Throwable) {}
    }

    private function upsertServiceEntrance(array $data): void
    {
        $data['project_id'] = $this->projectId;
        $data['updated_at'] = now();

        $existing = DB::table('project_service_entrance')
            ->where('project_id', $this->projectId)
            ->first();

        if ($existing) {
            DB::table('project_service_entrance')
                ->where('id', $existing->id)
                ->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('project_service_entrance')->insert($data);
        }
    }

    // ─── Loaders para steps 3, 5-10 ──────────────────────

    private function loadLoadsFromDb(): void
    {
        if (!$this->projectId) return;

        try {
            $rows = DB::table('project_loads')
                ->where('project_id', $this->projectId)
                ->orderBy('room_id')
                ->orderBy('sort_order')
                ->get();

            $this->loads = [];
            foreach ($rows as $row) {
                $roomIndex = 0;
                $roomLabel = '';
                foreach ($this->rooms as $ri => $room) {
                    if (($room['id'] ?? null) == $row->room_id) {
                        $roomIndex = $ri;
                        $roomLabel = trim(($room['room_type'] ?? '') . ': ' . ($room['description'] ?? ''), ': ');
                        break;
                    }
                }
                $desc = $row->description ?? '';
                if ($row->load_type === 'TUG' && (bool)$row->is_auto) {
                    $room = $this->rooms[$roomIndex] ?? [];
                    if (empty($desc) || str_contains($desc, '400VA') || str_contains($desc, '350VA') || preg_match('/^TUG\s*\(\d+[\s×]*\d+VA\)$/i', $desc)) {
                        $desc = $this->formatTugDescription($room, (int)$row->quantity, (int)$row->power_va);
                    }
                }

                $this->loads[] = [
                    'id'                  => $row->id,
                    'room_id'             => $row->room_id,
                    'room_index'          => $roomIndex,
                    'room_label'          => $roomLabel,
                    'load_type'           => $row->load_type,
                    'description'         => $desc,
                    'quantity'            => (int)$row->quantity,
                    'power_va'            => (int)$row->power_va,
                    'unit_va'             => (int)($row->unit_va ?? 0),
                    'is_auto'             => (bool)$row->is_auto,
                    'sort_order'          => (int)$row->sort_order,
                    'phases'              => (int)($row->phases ?? 1),
                    'voltage_v'           => (int)($row->voltage_v ?? 127),
                    'installation_method' => $row->installation_method ?? 'B1',
                    'temperature_c'       => (int)($row->temperature_c ?? 30),
                    'grouped_circuits'    => (int)($row->grouped_circuits ?? 1),
                    'fp'                  => (float)($row->fp ?? 1.0),
                    'power_w'             => $row->power_w !== null ? (float)$row->power_w : null,
                    'current_a'           => $row->current_a !== null ? (float)$row->current_a : null,
                    'grouping_factor'     => $row->grouping_factor !== null ? (float)$row->grouping_factor : null,
                    'temperature_factor'  => $row->temperature_factor !== null ? (float)$row->temperature_factor : null,
                    'corrected_current_a' => $row->corrected_current_a !== null ? (float)$row->corrected_current_a : null,
                    'use_manual_va'              => (bool)($row->use_manual_va ?? false),
                    'manual_va'                  => $row->manual_va !== null ? (string)$row->manual_va : '',
                    'circuit_number'             => isset($row->circuit_number) && $row->circuit_number !== null ? (int)$row->circuit_number : null,
                    'split_original_va'          => isset($row->split_original_va) && $row->split_original_va !== null ? (int)$row->split_original_va : null,
                    'split_original_description' => isset($row->split_original_description) && $row->split_original_description !== null ? (string)$row->split_original_description : null,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error loading loads from DB: ' . $e->getMessage());
        }
    }

    private function loadCircuitOverrides(): void
    {
        if (!$this->projectId) return;
        try {
            $rows = DB::table('project_circuit_calculations')
                ->where('project_id', $this->projectId)
                ->orderBy('circuit_number')
                ->get();

            foreach ($rows as $row) {
                $circNum = (int) $row->circuit_number;
                $this->circuitOverrides[$circNum] = [
                    'distance_m'           => (float) ($row->distance_m ?? 0.0),
                    'installation_method'  => (string) ($row->installation_method ?? 'B1'),
                    'temperature_c'        => (int) ($row->temperature_c ?? 30),
                    'grouped_circuits'     => (int) ($row->grouped_circuits ?? 1),
                    'voltage_drop_percent' => (float) ($row->voltage_drop_percent ?? 4.0),
                    'use_manual_conductor' => (bool) ($row->use_manual_final_conductor ?? false),
                    'manual_conductor_mm2' => $row->final_conductor_manual_mm2 !== null
                        ? (string) $row->final_conductor_manual_mm2 : '',
                    'use_manual_breaker'   => (bool) ($row->use_manual_breaker ?? false),
                    'manual_breaker_a'     => $row->breaker_manual_a !== null
                        ? (string) $row->breaker_manual_a : '',
                ];
            }
        } catch (\Throwable) {}
    }

    private function loadPhaseAssignments(): void
    {
        if (!$this->projectId) return;
        try {
            $circuits = DB::table('project_circuit_calculations')
                ->where('project_id', $this->projectId)
                ->orderBy('circuit_number')
                ->get();

            foreach ($circuits as $circuit) {
                $circNum = (int) $circuit->circuit_number;
                try {
                    $phRow = DB::table('project_phase_distribution')
                        ->where('project_id', $this->projectId)
                        ->where('circuit_number', $circNum)
                        ->first();

                    if ($phRow) {
                        $this->phaseAssignments[$circNum] = $phRow->use_phase_s ? 's'
                            : ($phRow->use_phase_t ? 't' : 'r');
                    } else {
                        $this->phaseAssignments[$circNum] = 'r';
                    }
                } catch (\Throwable) {
                    $this->phaseAssignments[$circNum] = 'r';
                }
            }
        } catch (\Throwable) {}
    }

    private function loadServiceEntrance(): void
    {
        if (!$this->projectId) return;
        try {
            $se = DB::table('project_service_entrance')
                ->where('project_id', $this->projectId)
                ->first();
            if ($se) {
                $this->sePhases        = (int) ($se->phases ?? 1);
                $this->sePolePosition  = $se->pole_position ?? '';
                $this->seNotes         = $se->notes ?? '';
                $this->shortCircuitKa  = (float) ($se->short_circuit_ka ?? 5.0);
                $this->hasNeutral      = $se->has_neutral ?? 'SIM';
                $this->dpsLocationType = $se->dps_location_type ?? 'Descargas Indiretas';
                $this->dpsVoltage      = (int) ($se->dps_voltage ?? 127);
            }
        } catch (\Throwable) {}
    }

    private function updateProgress(): void
    {
        if (!$this->projectId) return;
        $percent = round(($this->currentStep / self::TOTAL_STEPS) * 100, 2);
        Project::where('id', $this->projectId)->update([
            'progress_step'    => $this->currentStep,
            'progress_percent' => $percent,
        ]);
    }

    // ─── Step 8: Auto-distribuição de fases ──────────────

    public function autoAssignPhases(): void
    {
        if (!$this->projectId) return;

        try {
            $circuits = DB::table('project_circuit_calculations')
                ->where('project_id', $this->projectId)
                ->orderByDesc('power_va')
                ->get();

            $totals = ['r' => 0.0, 's' => 0.0, 't' => 0.0];

            foreach ($circuits as $circuit) {
                $circNum = (int) $circuit->circuit_number;
                $phases  = (int) ($circuit->phases ?? 1);
                $powerVa = (float) ($circuit->power_va ?? 0);

                if ($phases === 3) {
                    $this->phaseAssignments[$circNum] = 'r';
                    $totals['r'] += $powerVa / 3;
                    $totals['s'] += $powerVa / 3;
                    $totals['t'] += $powerVa / 3;
                } else {
                    $minPhase = array_keys($totals, min($totals))[0];
                    $this->phaseAssignments[$circNum] = $minPhase;
                    $totals[$minPhase] += $powerVa;
                }
            }

            $this->successMessage = 'Fases distribuídas automaticamente!';
        } catch (\Throwable $e) {
            $this->errorMessage = 'Erro ao distribuir fases: ' . $e->getMessage();
        }
    }

    // ─── CRUD de cômodos (Step 2) ─────────────────────────

    public function addRoom(): void
    {
        $this->rooms[] = $this->emptyRoom(count($this->rooms));
        $this->dispatch('room-added');
    }

    public function removeRoom(int $index): void
    {
        if (!isset($this->rooms[$index])) return;
        $room = $this->rooms[$index];
        if (!empty($room['id'])) {
            // Remove loads for this room
            try {
                DB::table('project_loads')->where('room_id', $room['id'])->delete();
            } catch (\Throwable) {}
            $this->loads = array_values(array_filter(
                $this->loads,
                fn($l) => ($l['room_id'] ?? null) != $room['id']
            ));
            ProjectInputRow::where('room_id', $room['id'])->delete();
            ProjectRoom::destroy($room['id']);
        }
        array_splice($this->rooms, $index, 1);
        $this->rooms = array_values($this->rooms);
        $this->dispatch('toast', type: 'error', message: 'Cômodo removido.');
    }

    // ─── Step 2: editor de planta baixa ──────────────────

    public function toggleFloorPlanEditor(): void
    {
        $this->showFloorPlanEditor = !$this->showFloorPlanEditor;
    }

    /**
     * Recebe os cômodos fechados desenhados no editor de planta baixa
     * (nome, área e perímetro medidos automaticamente) e os transforma em
     * linhas de $rooms, reaproveitando o mesmo cálculo de cargas mínimas
     * (iluminação/TUG) usado no cadastro manual.
     */
    public function importRoomsFromFloorPlan(array $comodos): void
    {
        $imported = 0;
        foreach ($comodos as $c) {
            if (empty($c['fechado'])) continue;

            $nome      = trim((string)($c['nome'] ?? ''));
            $area      = $c['area_m2']     ?? null;
            $perimetro = $c['perimetro_m'] ?? null;
            if ($area === null && $perimetro === null) continue;

            $room = $this->emptyRoom(count($this->rooms));
            $room['room_type']   = $this->guessRoomType($nome);
            $room['description'] = $nome;
            $room['area_m2']     = $area      !== null ? (string)$area      : '';
            $room['perimeter_m'] = $perimetro !== null ? (string)$perimetro : '';

            $this->rooms[] = $room;
            $this->calculateRoomLoads(count($this->rooms) - 1);
            $imported++;
        }

        if ($imported > 0) {
            $this->showFloorPlanEditor = false;
            $this->successMessage = "{$imported} cômodo(s) importado(s) da planta baixa. Confira o tipo de cada cômodo (foi adivinhado pelo nome) antes de avançar.";
            $this->dispatch('room-added');
        } else {
            $this->errorMessage = 'Nenhum cômodo fechado foi recebido da planta — desenhe as paredes ao redor e nomeie os cômodos antes de enviar.';
        }
    }

    /**
     * Tenta adivinhar o tipo de cômodo (NBR/ROOM_TYPES) a partir do nome
     * dado pelo aluno no editor de planta (ex: "Quarto 1" -> "QUARTO").
     * Cai em "OUTRO" quando não reconhece — o aluno ajusta manualmente.
     */
    private function guessRoomType(string $nome): string
    {
        $accentMap = ['Á'=>'A','Ã'=>'A','Â'=>'A','À'=>'A','É'=>'E','Ê'=>'E','Í'=>'I','Ó'=>'O','Õ'=>'O','Ô'=>'O','Ú'=>'U','Ç'=>'C'];
        $flat = strtr(mb_strtoupper($nome, 'UTF-8'), $accentMap);

        $map = [
            'SUITE'            => 'SUÍTE',
            'DORMIT'           => 'DORMITÓRIO',
            'QUARTO'           => 'QUARTO',
            'SALA DE JANTAR'   => 'SALA DE JANTAR',
            'SALA'             => 'SALA',
            'COZINHA-AREA'     => 'COZINHA-ÁREA DE SERVIÇO',
            'COPA-COZINHA'     => 'COPA-COZINHA',
            'COZINHA'          => 'COZINHA',
            'COPA'             => 'COPA',
            'LAVANDERIA'       => 'LAVANDERIA',
            'AREA DE SERVICO'  => 'ÁREA DE SERVIÇO',
            'SERVICO'          => 'ÁREA DE SERVIÇO',
            'LAVABO'           => 'LAVABO',
            'BANHEIRO'         => 'BANHEIRO',
            'WC'               => 'BANHEIRO',
            'GARAGEM'          => 'GARAGEM',
            'VARANDA'          => 'VARANDA',
            'TERRACO'          => 'TERRAÇO',
            'CORREDOR'         => 'CORREDOR',
            'HALL'             => 'HALL',
            'ESCADA'           => 'ESCADA',
            'CLOSET'           => 'CLOSET',
            'ESCRITORIO'       => 'ESCRITÓRIO',
            'AREA EXTERNA'     => 'ÁREA EXTERNA',
        ];

        foreach ($map as $needle => $type) {
            $needleFlat = strtr($needle, $accentMap);
            if (str_contains($flat, $needleFlat)) {
                return in_array($type, self::ROOM_TYPES, true) ? $type : 'OUTRO';
            }
        }
        return 'OUTRO';
    }

    // ─── Step 2: Cálculo automático de cargas mínimas ────

    public function calculateRoomLoads(int $index): void
    {
        if (!isset($this->rooms[$index])) return;
        $room = &$this->rooms[$index];

        $area  = (float)($room['area_m2']    ?? 0);
        $perim = (float)($room['perimeter_m'] ?? 0);
        $type  = (string)($room['room_type']  ?? '');

        if ($area > 0) {
            $room['lighting_va_calculated']    = $this->calcMinLightingVa($area);
            $room['lighting_rule_description'] = '100 VA até 6 m² + 60 VA a cada 4 m² inteiros adicionais (NBR 5410).';
        }

        $normalized = $this->normalizeRoomTypeForCalc($type);
        if ($perim > 0 || in_array($normalized, ['BANHEIRO', 'LAVABO'], true)) {
            $tugData = $this->calcMinTugData($type, $area, $perim);
            $room['tug_rule_group']          = $tugData['rule_group'];
            $room['tug_qty_calculated']      = $tugData['qty'];
            $room['tug_qty_600_calculated']  = $tugData['qty_600'];
            $room['tug_qty_100_calculated']  = $tugData['qty_100'];
            $room['tug_va_calculated']       = $tugData['total_va'];
            $room['tug_rule_description']    = $tugData['rule_description'];
        }

        $lightingVa = ($room['use_manual_lighting'] && $room['lighting_va_manual'] !== '')
            ? (int)$room['lighting_va_manual']
            : (int)($room['lighting_va_calculated'] ?? 0);

        if (!empty($room['use_manual_tug_qty'])) {
            if (($room['tug_qty_600_manual'] ?? '') === '') {
                $room['tug_qty_600_manual'] = (string)($room['tug_qty_600_calculated'] ?? 0);
            }
            if (($room['tug_qty_100_manual'] ?? '') === '') {
                $room['tug_qty_100_manual'] = (string)($room['tug_qty_100_calculated'] ?? 0);
            }

            $qty600 = (int)$room['tug_qty_600_manual'];
            $qty100 = (int)$room['tug_qty_100_manual'];

            $tugQty = $qty600 + $qty100;
            $tugVa  = ($qty600 * 600) + ($qty100 * 100);

            $room['tug_qty_manual'] = (string)$tugQty;
            $room['tug_va_manual']  = (string)$tugVa;
        } else {
            $tugQty = (int)($room['tug_qty_calculated'] ?? 0);
            $tugVa  = (int)($room['tug_va_calculated'] ?? 0);
        }

        $room['total_minimum_va_calculated'] = $lightingVa + $tugVa;
        $room['total_minimum_va_final']      = $room['total_minimum_va_calculated'];

        unset($room);
    }

    public function recalculateAllRooms(): void
    {
        foreach (array_keys($this->rooms) as $i) {
            $this->calculateRoomLoads($i);
        }
        $this->successMessage = 'Todas as cargas recalculadas.';
    }

    public function resetRoomManual(int $index, string $field): void
    {
        if (!isset($this->rooms[$index])) return;
        $this->rooms[$index][$field] = false;
        if ($field === 'use_manual_lighting') {
            $this->rooms[$index]['lighting_va_manual'] = '';
        } elseif ($field === 'use_manual_tug_qty') {
            $this->rooms[$index]['tug_qty_manual'] = '';
            $this->rooms[$index]['tug_qty_600_manual'] = '';
            $this->rooms[$index]['tug_qty_100_manual'] = '';
            $this->rooms[$index]['tug_va_manual'] = '';
            $this->rooms[$index]['use_manual_tug_va'] = false;
        } elseif ($field === 'use_manual_tug_va') {
            $this->rooms[$index]['tug_va_manual'] = '';
            $this->rooms[$index]['use_manual_tug_va'] = false;
        }
        $this->calculateRoomLoads($index);
    }

    // ─── Step 3: Geração e cálculo de cargas ─────────────

    public function generateLoadsFromRooms(): void
    {
        $this->pushHistory();
        // Separate existing TUEs (manual) and auto loads
        $existingTues     = [];
        $existingAutoLoads = [];

        if (empty($this->loads) && $this->projectId) {
            try {
                $dbTues = DB::table('project_loads')
                    ->where('project_id', $this->projectId)
                    ->where(function($q) {
                        $q->where('is_auto', 0)
                          ->orWhere('load_type', 'TUE');
                    })
                    ->get();
                foreach ($dbTues as $tRow) {
                    $rid = (string)$tRow->room_id;
                    $roomIndex = 0;
                    $roomLabel = '';
                    foreach ($this->rooms as $ri => $room) {
                        if (($room['id'] ?? null) == $tRow->room_id) {
                            $roomIndex = $ri;
                            $roomLabel = trim(($room['room_type'] ?? '') . ': ' . ($room['description'] ?? ''), ': ');
                            break;
                        }
                    }
                    $existingTues[$rid][] = [
                        'id'                  => $tRow->id,
                        'room_id'             => $tRow->room_id,
                        'room_index'          => $roomIndex,
                        'room_label'          => $roomLabel,
                        'load_type'           => $tRow->load_type ?: 'TUE',
                        'description'         => $tRow->description ?? '',
                        'quantity'            => (int)($tRow->quantity ?? 1),
                        'power_va'            => (int)($tRow->power_va ?? 0),
                        'unit_va'             => (int)($tRow->unit_va ?? 0),
                        'is_auto'             => false,
                        'sort_order'          => (int)($tRow->sort_order ?? 10),
                        'phases'              => (int)($tRow->phases ?? $this->defaultPhases),
                        'voltage_v'           => (int)($tRow->voltage_v ?? $this->defaultVoltage),
                        'installation_method' => $tRow->installation_method ?? $this->defaultInstallationMethod,
                        'temperature_c'       => (int)($tRow->temperature_c ?? $this->defaultTemperatureC),
                        'grouped_circuits'    => (int)($tRow->grouped_circuits ?? 1),
                        'fp'                  => (float)($tRow->fp ?? 1.0),
                        'power_w'             => $tRow->power_w !== null ? (float)$tRow->power_w : null,
                        'current_a'           => $tRow->current_a !== null ? (float)$tRow->current_a : null,
                        'grouping_factor'     => $tRow->grouping_factor !== null ? (float)$tRow->grouping_factor : null,
                        'temperature_factor'  => $tRow->temperature_factor !== null ? (float)$tRow->temperature_factor : null,
                        'corrected_current_a' => $tRow->corrected_current_a !== null ? (float)$tRow->corrected_current_a : null,
                        'use_manual_va'              => (bool)($tRow->use_manual_va ?? false),
                        'manual_va'                  => $tRow->manual_va !== null ? (string)$tRow->manual_va : '',
                        'circuit_number'             => isset($tRow->circuit_number) && $tRow->circuit_number !== null ? (int)$tRow->circuit_number : null,
                        'split_original_va'          => isset($tRow->split_original_va) && $tRow->split_original_va !== null ? (int)$tRow->split_original_va : null,
                        'split_original_description' => isset($tRow->split_original_description) && $tRow->split_original_description !== null ? (string)$tRow->split_original_description : null,
                    ];
                }
            } catch (\Throwable) {}
        }

        foreach ($this->loads as $load) {
            $rid = (string)($load['room_id'] ?? '');
            if (!($load['is_auto'] ?? false)) {
                $existingTues[$rid][] = $load;
            } else {
                $key = $rid . '_' . ($load['load_type'] ?? '');
                $existingAutoLoads[$key] = $load;
            }
        }

        $newLoads = [];

        foreach ($this->rooms as $ri => $room) {
            $roomId    = $room['id'] ?? null;
            $roomLabel = trim(($room['room_type'] ?? '') . ': ' . ($room['description'] ?? ''), ': ');
            $rid       = (string)$roomId;

            // Effective lighting VA from Step 2
            $lightingVa = 0;
            if (($room['use_manual_lighting'] ?? false) && ($room['lighting_va_manual'] ?? '') !== '') {
                $lightingVa = (int)$room['lighting_va_manual'];
            } elseif (($room['lighting_va_calculated'] ?? null) !== null) {
                $lightingVa = (int)$room['lighting_va_calculated'];
            }

            // Effective TUG from Step 2
            $tugVa  = 0;
            $tugQty = 0;
            $qty600 = 0;
            $qty100 = 0;
            if (!empty($room['use_manual_tug_qty'])) {
                $qty600 = (($room['tug_qty_600_manual'] ?? '') !== '') ? (int)$room['tug_qty_600_manual'] : (int)($room['tug_qty_600_calculated'] ?? 0);
                $qty100 = (($room['tug_qty_100_manual'] ?? '') !== '') ? (int)$room['tug_qty_100_manual'] : (int)($room['tug_qty_100_calculated'] ?? 0);
                $tugQty = $qty600 + $qty100;
                $tugVa  = ($qty600 * 600) + ($qty100 * 100);
            } elseif (($room['tug_va_calculated'] ?? null) !== null) {
                $tugVa  = (int)$room['tug_va_calculated'];
                $tugQty = (int)($room['tug_qty_calculated'] ?? 0);
                $qty600 = (int)($room['tug_qty_600_calculated'] ?? 0);
                $qty100 = (int)($room['tug_qty_100_calculated'] ?? 0);
            }
            $tugUnitVa = ($tugQty > 0 && $tugVa > 0) ? (int)round($tugVa / $tugQty) : 0;

            $defaults = [
                'floor_number'        => (int)($room['floor_number'] ?? 1),
                'phases'              => $this->defaultPhases,
                'voltage_v'           => $this->defaultVoltage,
                'installation_method' => $this->defaultInstallationMethod,
                'temperature_c'       => $this->defaultTemperatureC,
                'grouped_circuits'    => 1,
                'fp'                  => 1.0,
                'power_w'             => null,
                'current_a'           => null,
                'grouping_factor'     => null,
                'temperature_factor'  => null,
                'corrected_current_a' => null,
                'use_manual_va'              => false,
                'manual_va'                  => '',
                'circuit_number'             => null,
                'split_original_va'          => null,
                'split_original_description' => null,
            ];

            // ILUMINAÇÃO
            if ($lightingVa > 0) {
                $existing = $existingAutoLoads["{$rid}_ILUMINAÇÃO"] ?? null;
                $load = $existing ? array_merge($existing, [
                    'room_id'    => $roomId,
                    'room_index' => $ri,
                    'room_label' => $roomLabel,
                    'power_va'   => $lightingVa,
                    'unit_va'    => $lightingVa,
                ]) : array_merge($defaults, [
                    'id'          => null,
                    'room_id'     => $roomId,
                    'room_index'  => $ri,
                    'room_label'  => $roomLabel,
                    'load_type'   => 'ILUMINAÇÃO',
                    'description' => 'Iluminação',
                    'quantity'    => 1,
                    'power_va'    => $lightingVa,
                    'unit_va'     => $lightingVa,
                    'is_auto'     => true,
                    'sort_order'  => 0,
                ]);
                $newLoads[] = $load;
            }

            // TUG
            if ($tugVa > 0 && $tugQty > 0) {
                if ($qty600 > 0 && $qty100 > 0) {
                    // 1. TUG 600VA
                    $desc600 = "TUG ({$qty600}× 600VA)";
                    $existing600 = $existingAutoLoads["{$rid}_TUG_600"] ?? $existingAutoLoads["{$rid}_TUG"] ?? null;
                    $load600 = $existing600 ? array_merge($existing600, [
                        'room_id'     => $roomId,
                        'room_index'  => $ri,
                        'room_label'  => $roomLabel,
                        'power_va'    => $qty600 * 600,
                        'unit_va'     => 600,
                        'quantity'    => $qty600,
                        'description' => $desc600,
                    ]) : array_merge($defaults, [
                        'id'          => null,
                        'room_id'     => $roomId,
                        'room_index'  => $ri,
                        'room_label'  => $roomLabel,
                        'load_type'   => 'TUG',
                        'description' => $desc600,
                        'quantity'    => $qty600,
                        'power_va'    => $qty600 * 600,
                        'unit_va'     => 600,
                        'is_auto'     => true,
                        'sort_order'  => 1,
                    ]);
                    $newLoads[] = $load600;

                    // 2. TUG 100VA
                    $desc100 = "TUG ({$qty100}× 100VA)";
                    $existing100 = $existingAutoLoads["{$rid}_TUG_100"] ?? null;
                    $load100 = $existing100 ? array_merge($existing100, [
                        'room_id'     => $roomId,
                        'room_index'  => $ri,
                        'room_label'  => $roomLabel,
                        'power_va'    => $qty100 * 100,
                        'unit_va'     => 100,
                        'quantity'    => $qty100,
                        'description' => $desc100,
                    ]) : array_merge($defaults, [
                        'id'          => null,
                        'room_id'     => $roomId,
                        'room_index'  => $ri,
                        'room_label'  => $roomLabel,
                        'load_type'   => 'TUG',
                        'description' => $desc100,
                        'quantity'    => $qty100,
                        'power_va'    => $qty100 * 100,
                        'unit_va'     => 100,
                        'is_auto'     => true,
                        'sort_order'  => 2,
                    ]);
                    $newLoads[] = $load100;
                } else {
                    $parts = [];
                    if ($qty600 > 0) $parts[] = "{$qty600}× 600VA";
                    if ($qty100 > 0) $parts[] = "{$qty100}× 100VA";
                    if (empty($parts)) {
                        $parts[] = "{$tugQty}× {$tugUnitVa}VA";
                    }
                    $tugDesc  = "TUG (" . implode(' + ', $parts) . ")";
                    $existing = $existingAutoLoads["{$rid}_TUG"] ?? null;
                    $load = $existing ? array_merge($existing, [
                        'room_id'     => $roomId,
                        'room_index'  => $ri,
                        'room_label'  => $roomLabel,
                        'power_va'    => $tugVa,
                        'unit_va'     => $qty600 > 0 ? 600 : 100,
                        'quantity'    => $tugQty,
                        'description' => $tugDesc,
                    ]) : array_merge($defaults, [
                        'id'          => null,
                        'room_id'     => $roomId,
                        'room_index'  => $ri,
                        'room_label'  => $roomLabel,
                        'load_type'   => 'TUG',
                        'description' => $tugDesc,
                        'quantity'    => $tugQty,
                        'power_va'    => $tugVa,
                        'unit_va'     => $qty600 > 0 ? 600 : 100,
                        'is_auto'     => true,
                        'sort_order'  => 1,
                    ]);
                    $newLoads[] = $load;
                }
            }

            // Preserve existing TUEs for this room
            foreach ($existingTues[$rid] ?? [] as $tue) {
                $tue['room_index'] = $ri;
                $tue['room_label'] = $roomLabel;
                $newLoads[] = $tue;
            }
        }

        $this->loads = $newLoads;
        $this->sortLoadsBySequence();

        foreach (array_keys($this->loads) as $li) {
            $this->calculateLoad($li);
        }

        $this->successMessage = 'Cargas atualizadas a partir dos cômodos.';
    }

    private function sortLoadsBySequence(): void
    {
        usort($this->loads, function ($a, $b) {
            $circA = (int)($a['circuit_number'] ?? 0);
            $circB = (int)($b['circuit_number'] ?? 0);
            if ($circA > 0 && $circB > 0 && $circA !== $circB) return $circA <=> $circB;
            if ($circA > 0 && $circB <= 0) return -1;
            if ($circA <= 0 && $circB > 0) return 1;

            $typeOrder = ['ILUMINAÇÃO' => 1, 'TUG' => 2, 'TUE' => 3];
            $tA = $typeOrder[$a['load_type'] ?? ''] ?? 99;
            $tB = $typeOrder[$b['load_type'] ?? ''] ?? 99;
            if ($tA !== $tB) return $tA <=> $tB;

            $vA = (int)($a['voltage_v'] ?? 127);
            $vB = (int)($b['voltage_v'] ?? 127);
            if ($vA !== $vB) return $vA <=> $vB;

            $flA = (int)($a['floor_number'] ?? 1);
            $flB = (int)($b['floor_number'] ?? 1);
            if ($flA !== $flB) return $flA <=> $flB;

            $rA = (int)($a['room_index'] ?? 0);
            $rB = (int)($b['room_index'] ?? 0);
            if ($rA !== $rB) return $rA <=> $rB;

            return (int)($a['sort_order'] ?? 0) <=> (int)($b['sort_order'] ?? 0);
        });
    }

    public function addTueLoad(int $roomIndex): void
    {
        if (!isset($this->rooms[$roomIndex])) return;
        $this->pushHistory();
        $room      = $this->rooms[$roomIndex];
        $roomId    = $room['id'] ?? null;
        $roomLabel = trim(($room['room_type'] ?? '') . ': ' . ($room['description'] ?? ''), ': ');

        $tueCount = count(array_filter($this->loads, fn($l) =>
            ($l['room_id'] ?? null) == $roomId && ($l['load_type'] ?? '') === 'TUE'
        ));

        $this->loads[] = [
            'id'                  => null,
            'room_id'             => $roomId,
            'room_index'          => $roomIndex,
            'room_label'          => $roomLabel,
            'load_type'           => 'TUE',
            'description'         => '',
            'quantity'            => 1,
            'power_va'            => 0,
            'unit_va'             => 0,
            'is_auto'             => false,
            'sort_order'          => 10 + $tueCount,
            'phases'              => $this->defaultPhases,
            'voltage_v'           => $this->defaultVoltage,
            'installation_method' => $this->defaultInstallationMethod,
            'temperature_c'       => $this->defaultTemperatureC,
            'grouped_circuits'    => 1,
            'fp'                  => 1.0,
            'power_w'             => null,
            'current_a'           => null,
            'grouping_factor'     => null,
            'temperature_factor'  => null,
            'corrected_current_a' => null,
            'use_manual_va'              => false,
            'manual_va'                  => '',
            'circuit_number'             => null,
            'split_original_va'          => null,
            'split_original_description' => null,
        ];
    }

    public function removeLoad(int $index): void
    {
        if (!isset($this->loads[$index])) return;
        $this->pushHistory();
        $load = $this->loads[$index];
        if (!empty($load['id'])) {
            try { DB::table('project_loads')->where('id', $load['id'])->delete(); } catch (\Throwable) {}
        }
        array_splice($this->loads, $index, 1);
        $this->loads = array_values($this->loads);
    }

    public function calculateLoad(int $index): void
    {
        if (!isset($this->loads[$index])) return;
        $load = &$this->loads[$index];

        $va      = ($load['use_manual_va'] ?? false) && ($load['manual_va'] ?? '') !== ''
            ? (int)$load['manual_va']
            : (int)($load['power_va'] ?? 0);
        $phases  = (int)($load['phases']           ?? 1);
        $voltage = max(1, (int)($load['voltage_v'] ?? 127));
        $fp      = max(0.01, (float)($load['fp']   ?? 1.0));
        $grouped = max(1, (int)($load['grouped_circuits'] ?? 1));
        $tempC   = (int)($load['temperature_c']    ?? 30);

        $load['power_w']          = round($va * $fp, 2);
        $load['current_a']        = $phases === 3
            ? round($va / (sqrt(3) * $voltage), 4)
            : round($va / $voltage, 4);
        $load['grouping_factor']   = $this->calcGroupingFactor($grouped);
        $load['temperature_factor']= $this->calcTemperatureFactor($tempC);

        $divisor = ($load['grouping_factor'] * $load['temperature_factor']);
        $load['corrected_current_a'] = $divisor > 0
            ? round($load['current_a'] / $divisor, 4)
            : $load['current_a'];

        unset($load);
    }

    public function recalculateAllLoads(): void
    {
        foreach (array_keys($this->loads) as $li) {
            $this->calculateLoad($li);
        }
        $this->successMessage = 'Todas as cargas recalculadas.';
    }

    public function resetLoadManual(int $index): void
    {
        if (!isset($this->loads[$index])) return;
        $this->loads[$index]['use_manual_va'] = false;
        $this->loads[$index]['manual_va']     = '';
        $this->calculateLoad($index);
    }

    // ─── NBR 5410 ─────────────────────────────────────────

    private function calcMinLightingVa(float $area): int
    {
        if ($area <= 0) return 0;
        if ($area < 6.0) return 100;
        return 100 + (int)(floor(($area - 6.0) / 4.0) * 60);
    }

    private function normalizeRoomTypeForCalc(string $roomType): string
    {
        $str = mb_strtoupper(trim($roomType));
        return strtr($str, [
            'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I',
            'Ó' => 'O', 'Ò' => 'O', 'Õ' => 'O', 'Ô' => 'O',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U',
            'Ç' => 'C',
        ]);
    }

    private function calcMinTugData(string $roomType, float $area, float $perimeter): array
    {
        $norm = $this->normalizeRoomTypeForCalc($roomType);

        $tipoA = ['COZINHA', 'COPA', 'COPA-COZINHA', 'AREA DE SERVICO', 'LAVANDERIA', 'COZINHA-AREA DE SERVICO'];
        $tipoB = ['BANHEIRO', 'LAVABO'];
        $tipoD = ['HALL', 'ESCADA', 'VARANDA', 'GARAGEM', 'TERRACO'];

        if (in_array($norm, $tipoA, true)) {
            $qty    = max(1, (int)ceil($perimeter / 3.5));
            $qty600 = min(3, $qty);
            $qty100 = max(0, $qty - 3);
            $va     = ($qty600 * 600) + ($qty100 * 100);
            return [
                'qty'              => $qty,
                'qty_600'          => $qty600,
                'qty_100'          => $qty100,
                'total_va'         => $va,
                'unit_va'          => 600,
                'rule_group'       => 'TIPO_A_COZINHA_COPA_SERVICO',
                'rule_description' => '1 tomada a cada 3,5 m ou fração. As 3 primeiras com 600 VA, as demais com 100 VA.',
            ];
        }

        if (in_array($norm, $tipoB, true)) {
            return [
                'qty'              => 1,
                'qty_600'          => 1,
                'qty_100'          => 0,
                'total_va'         => 600,
                'unit_va'          => 600,
                'rule_group'       => 'TIPO_B_BANHEIRO',
                'rule_description' => 'Mínimo de 1 tomada de 600 VA junto ao lavatório.',
            ];
        }

        if (in_array($norm, $tipoD, true)) {
            return [
                'qty'              => 1,
                'qty_600'          => 0,
                'qty_100'          => 1,
                'total_va'         => 100,
                'unit_va'          => 100,
                'rule_group'       => 'TIPO_D_HALL_ESCADA_VARANDA_GARAGEM',
                'rule_description' => 'Mínimo de 1 tomada de 100 VA.',
            ];
        }

        // Tipo C — social / íntimo / geral
        $qty = ($area <= 6.0 || $perimeter <= 0) ? 1 : max(1, (int)ceil($perimeter / 5.0));
        return [
            'qty'              => $qty,
            'qty_600'          => 0,
            'qty_100'          => $qty,
            'total_va'         => $qty * 100,
            'unit_va'          => 100,
            'rule_group'       => 'TIPO_C_SOCIAL_INTIMO_GERAL',
            'rule_description' => 'Área ≤ 6 m²: 1 tomada. Acima: 1 tomada a cada 5 m ou fração (100 VA cada).',
        ];
    }

    private function calcGroupingFactor(int $count): float
    {
        return match (true) {
            $count <= 1  => 1.00,
            $count === 2 => 0.80,
            $count === 3 => 0.70,
            $count === 4 => 0.65,
            $count === 5 => 0.60,
            $count === 6 => 0.57,
            $count === 7 => 0.54,
            $count === 8 => 0.52,
            $count <= 11 => 0.50,
            $count <= 15 => 0.45,
            $count <= 19 => 0.41,
            default      => 0.38,
        };
    }

    private function calcTemperatureFactor(int $tempC): float
    {
        return match (true) {
            $tempC <= 10  => 1.22,
            $tempC <= 15  => 1.17,
            $tempC <= 20  => 1.12,
            $tempC <= 25  => 1.06,
            $tempC <= 30  => 1.00,
            $tempC <= 35  => 0.94,
            $tempC <= 40  => 0.87,
            $tempC <= 45  => 0.79,
            $tempC <= 50  => 0.71,
            $tempC <= 55  => 0.61,
            default       => 0.50,
        };
    }

    // ─── Step 4: Atribuição de Circuitos ─────────────────

    public function getCircuitPreviews(): array
    {
        $circuits = [];

        foreach ($this->loads as $load) {
            $circNum = $load['circuit_number'] ?? null;
            if ($circNum === null || $circNum === '' || (int)$circNum <= 0) continue;
            $circNum = (int)$circNum;

            $va = ($load['use_manual_va'] ?? false) && ($load['manual_va'] ?? '') !== ''
                ? (int)$load['manual_va']
                : (int)($load['power_va'] ?? 0);

            if (!isset($circuits[$circNum])) {
                $circuits[$circNum] = [
                    'loads'        => [],
                    'types'        => [],
                    'voltages'     => [],
                    'phases_list'  => [],
                    'room_labels'  => [],
                    'total_va'     => 0,
                    'total_w'      => 0,
                ];
            }

            $circuits[$circNum]['loads'][]        = $load;
            $circuits[$circNum]['types'][]        = $load['load_type'] ?? 'ILUMINAÇÃO';
            $circuits[$circNum]['voltages'][]     = (int)($load['voltage_v'] ?? 127);
            $circuits[$circNum]['phases_list'][]  = (int)($load['phases'] ?? 1);
            // Strip "TYPE: " prefix — keep only the description part
            $rawLabel  = $load['room_label'] ?? '';
            $colonPos  = mb_strpos($rawLabel, ': ');
            $circuits[$circNum]['room_labels'][] = $colonPos !== false
                ? mb_substr($rawLabel, $colonPos + 2)
                : $rawLabel;
            $circuits[$circNum]['total_va']       += $va;
            $circuits[$circNum]['total_w']        += (float)($load['power_w'] ?? ($va * (float)($load['fp'] ?? 1.0)));
        }

        $previews = [];
        foreach ($circuits as $circNum => $data) {
            $uniqueTypes    = array_values(array_unique($data['types']));
            $uniqueVoltages = array_values(array_unique($data['voltages']));
            $uniquePhases   = array_values(array_unique($data['phases_list']));

            sort($uniqueTypes);
            sort($uniqueVoltages);
            sort($uniquePhases);

            $circuitType = count($uniqueTypes) === 1 ? $uniqueTypes[0] : 'MISTO';
            $voltage     = $uniqueVoltages[0];
            $phases      = $uniquePhases[0];

            $totalVa     = $data['total_va'];
            $totalW      = $data['total_w'];
            $resultingFp = $totalVa > 0 ? round($totalW / $totalVa, 4) : 1.0;
            $currentA    = $phases === 3
                ? round($totalVa / (sqrt(3) * max(1, $voltage)), 4)
                : round($totalVa / max(1, $voltage), 4);

            $alerts     = [];
            $hasCritical = false;

            if (count($uniqueVoltages) > 1) {
                $alerts[] = ['level' => 'critical', 'msg' => 'Tensões mistas (' . implode('V + ', $uniqueVoltages) . 'V). Separe as cargas por tensão.'];
                $hasCritical = true;
            }
            if (count($uniquePhases) > 1) {
                $alerts[] = ['level' => 'critical', 'msg' => 'Número de fases misto (' . implode('F + ', $uniquePhases) . 'F). Separe as cargas por número de fases.'];
                $hasCritical = true;
            }

            $hasIlum = in_array('ILUMINAÇÃO', $data['types'], true);
            $hasTug  = in_array('TUG', $data['types'], true);
            $hasTue  = in_array('TUE', $data['types'], true);

            if ($hasIlum && $hasTug) {
                $alerts[] = ['level' => 'severe', 'msg' => 'ILUMINAÇÃO e TUG no mesmo circuito. NBR 5410 recomenda separação por tipo.'];
            }
            if ($hasTue && ($hasIlum || $hasTug)) {
                $alerts[] = ['level' => 'severe', 'msg' => 'TUE misturado com ILUMINAÇÃO/TUG. Circuitos de uso específico devem ser exclusivos.'];
            }

            $currentLimit = match ($circuitType) {
                'ILUMINAÇÃO' => 10.0,
                'TUG'        => 16.0,
                'TUE'        => 25.0,
                default      => 16.0,
            };
            if ($currentA > $currentLimit) {
                $alerts[] = ['level' => 'warning', 'msg' => sprintf('Corrente do circuito %.2fA supera o recomendado de %.0fA para %s.', $currentA, $currentLimit, $circuitType)];
            }

            $statusOrder = ['ok' => 0, 'warning' => 1, 'severe' => 2, 'critical' => 3];
            $status = 'ok';
            foreach ($alerts as $alert) {
                if (($statusOrder[$alert['level']] ?? 0) > ($statusOrder[$status] ?? 0)) {
                    $status = $alert['level'];
                }
            }

            // Detect split part label: single-load circuit whose description ends with "— pt X/Y"
            $partLabel = null;
            if (count($data['loads']) === 1) {
                $desc = $data['loads'][0]['description'] ?? '';
                if (preg_match('/— pt (\d+)\/(\d+)$/', $desc, $m)) {
                    $partLabel = 'parte ' . $m[1] . ' de ' . $m[2];
                }
            }

            $previews[$circNum] = [
                'circuit_number'   => $circNum,
                'circuit_type'     => $circuitType,
                'part_label'       => $partLabel,
                'total_loads'      => count($data['loads']),
                'total_power_va'   => $totalVa,
                'total_power_w'    => $totalW,
                'resulting_fp'     => $resultingFp,
                'voltage_v'        => $voltage,
                'phases'           => $phases,
                'current_a'        => $currentA,
                'status'           => $status,
                'alerts'           => $alerts,
                'has_critical_error' => $hasCritical,
                'loads'            => $data['loads'],
                'room_labels'      => array_values(array_unique(array_filter($data['room_labels']))),
            ];
        }

        ksort($previews);
        return $previews;
    }

    public function openDistributeModal(): void
    {
        $this->showDistributeModal = true;
    }

    public function closeDistributeModal(): void
    {
        $this->showDistributeModal = false;
    }

    public function resetCircuitAssignments(): void
    {
        $this->clearMessages();
        $this->pushHistory();
        $this->showDistributeModal = false;

        foreach (array_keys($this->loads) as $li) {
            $this->loads[$li]['circuit_number'] = null;
        }

        if ($this->projectId) {
            try {
                DB::table('project_loads')
                    ->where('project_id', $this->projectId)
                    ->update(['circuit_number' => null, 'updated_at' => now()]);

                DB::table('project_circuit_previews')
                    ->where('project_id', $this->projectId)
                    ->delete();

                DB::table('project_load_rows')
                    ->where('project_id', $this->projectId)
                    ->delete();

                DB::table('project_circuit_calculations')
                    ->where('project_id', $this->projectId)
                    ->delete();

                DB::table('project_phase_distribution')
                    ->where('project_id', $this->projectId)
                    ->delete();

                $roomIds = ProjectRoom::where('project_id', $this->projectId)->pluck('id')->toArray();
                if (!empty($roomIds)) {
                    ProjectInputRow::whereIn('room_id', $roomIds)->delete();
                }
            } catch (\Throwable) {}
        }

        $this->circuitOverrides = [];
        $this->phaseAssignments = [];
        $this->successMessage = 'Circuitos resetados. Todas as cargas estao sem circuito atribuido.';
    }

    public function autoAssignCircuits(): void
    {
        $this->clearMessages();
        $this->pushHistory();
        $this->showDistributeModal = false;

        foreach (array_keys($this->loads) as $li) {
            $this->loads[$li]['load_type'] = mb_strtoupper(trim((string)($this->loads[$li]['load_type'] ?? 'ILUMINAÇÃO')));
            $this->loads[$li]['circuit_number'] = null;
        }

        $nextCircuit = 1;
        $targetMinLighting = max(1, (int)$this->autoDistributeMinLighting);
        $targetMinTug      = max(1, (int)$this->autoDistributeMinTug);

        // Map room floors for quick lookup if needed
        $roomFloors = [];
        foreach ($this->rooms as $r) {
            if (isset($r['id'])) {
                $roomFloors[(string)$r['id']] = (int)($r['floor_number'] ?? 1);
            }
        }

        $getFloor = function(array $load) use ($roomFloors): int {
            if (isset($load['floor_number'])) return (int)$load['floor_number'];
            $rid = (string)($load['room_id'] ?? '');
            return $roomFloors[$rid] ?? 1;
        };

        $sortGroups = function (array &$groups) {
            uksort($groups, function ($keyA, $keyB) {
                [$vA, $phA, $flA] = array_map('intval', explode('_', $keyA));
                [$vB, $phB, $flB] = array_map('intval', explode('_', $keyB));

                if ($vA !== $vB) return $vA <=> $vB;
                if ($phA !== $phB) return $phA <=> $phB;
                return $flA <=> $flB;
            });
        };

        // 1. ILUMINAÇÃO (Sequência 1 em diante)
        $lightingIndices = [];
        foreach ($this->loads as $li => $load) {
            if (($load['load_type'] ?? '') === 'ILUMINAÇÃO') {
                $lightingIndices[] = $li;
            }
        }

        if (!empty($lightingIndices)) {
            $lightingGroups = [];
            foreach ($lightingIndices as $li) {
                $l = $this->loads[$li];
                $key = ((int)($l['voltage_v'] ?? 127)) . '_' . ((int)($l['phases'] ?? 1)) . '_' . $getFloor($l);
                $lightingGroups[$key][] = $li;
            }

            $sortGroups($lightingGroups);

            $createdLightingCircuits = [];
            foreach ($lightingGroups as $group) {
                $currentCircuit = [];
                $currentI = 0.0;
                foreach ($group as $li) {
                    $l = $this->loads[$li];
                    $va = ($l['use_manual_va'] ?? false) && ($l['manual_va'] ?? '') !== ''
                        ? (int)$l['manual_va']
                        : (int)($l['power_va'] ?? 0);
                    $v = max(1, (int)($l['voltage_v'] ?? 127));
                    $ph = (int)($l['phases'] ?? 1);
                    $loadI = $ph === 3 ? $va / (sqrt(3) * $v) : $va / $v;

                    if (!empty($currentCircuit) && ($currentI + $loadI) > 10.0) {
                        $createdLightingCircuits[] = $currentCircuit;
                        $currentCircuit = [];
                        $currentI = 0.0;
                    }
                    $currentCircuit[] = $li;
                    $currentI += $loadI;
                }
                if (!empty($currentCircuit)) {
                    $createdLightingCircuits[] = $currentCircuit;
                }
            }

            while (count($createdLightingCircuits) < $targetMinLighting) {
                $maxLoadCount = 1;
                $targetIdx = -1;
                foreach ($createdLightingCircuits as $idx => $circ) {
                    if (count($circ) > $maxLoadCount) {
                        $maxLoadCount = count($circ);
                        $targetIdx = $idx;
                    }
                }

                if ($targetIdx === -1) {
                    break;
                }

                $circToSplit = $createdLightingCircuits[$targetIdx];
                $half = (int) ceil(count($circToSplit) / 2);
                $part1 = array_slice($circToSplit, 0, $half);
                $part2 = array_slice($circToSplit, $half);

                $createdLightingCircuits[$targetIdx] = $part1;
                $createdLightingCircuits[] = $part2;
            }

            foreach ($createdLightingCircuits as $circ) {
                foreach ($circ as $li) {
                    $this->loads[$li]['circuit_number'] = $nextCircuit;
                }
                $nextCircuit++;
            }
        }

        // 2. TUG (Sequência logo após Iluminação)
        $tugIndices = [];
        foreach ($this->loads as $li => $load) {
            if (($load['load_type'] ?? '') === 'TUG') {
                $tugIndices[] = $li;
            }
        }

        if (!empty($tugIndices)) {
            $tugGroups = [];
            foreach ($tugIndices as $li) {
                $l = $this->loads[$li];
                $key = ((int)($l['voltage_v'] ?? 127)) . '_' . ((int)($l['phases'] ?? 1)) . '_' . $getFloor($l);
                $tugGroups[$key][] = $li;
            }

            $sortGroups($tugGroups);

            $createdTugCircuits = [];
            foreach ($tugGroups as $group) {
                $currentCircuit = [];
                $currentI = 0.0;
                foreach ($group as $li) {
                    $l = $this->loads[$li];
                    $va = ($l['use_manual_va'] ?? false) && ($l['manual_va'] ?? '') !== ''
                        ? (int)$l['manual_va']
                        : (int)($l['power_va'] ?? 0);
                    $v = max(1, (int)($l['voltage_v'] ?? 127));
                    $ph = (int)($l['phases'] ?? 1);
                    $loadI = $ph === 3 ? $va / (sqrt(3) * $v) : $va / $v;

                    if (!empty($currentCircuit) && ($currentI + $loadI) > 10.0) {
                        $createdTugCircuits[] = $currentCircuit;
                        $currentCircuit = [];
                        $currentI = 0.0;
                    }
                    $currentCircuit[] = $li;
                    $currentI += $loadI;
                }
                if (!empty($currentCircuit)) {
                    $createdTugCircuits[] = $currentCircuit;
                }
            }

            while (count($createdTugCircuits) < $targetMinTug) {
                $maxLoadCount = 1;
                $targetIdx = -1;
                foreach ($createdTugCircuits as $idx => $circ) {
                    if (count($circ) > $maxLoadCount) {
                        $maxLoadCount = count($circ);
                        $targetIdx = $idx;
                    }
                }

                if ($targetIdx === -1) {
                    break;
                }

                $circToSplit = $createdTugCircuits[$targetIdx];
                $half = (int) ceil(count($circToSplit) / 2);
                $part1 = array_slice($circToSplit, 0, $half);
                $part2 = array_slice($circToSplit, $half);

                $createdTugCircuits[$targetIdx] = $part1;
                $createdTugCircuits[] = $part2;
            }

            foreach ($createdTugCircuits as $circ) {
                foreach ($circ as $li) {
                    $this->loads[$li]['circuit_number'] = $nextCircuit;
                }
                $nextCircuit++;
            }
        }

        // 3. TUE (Sequência final - cada TUE em circuito exclusivo, 127V primeiro e 220V depois)
        $tueIndices = [];
        foreach ($this->loads as $li => $load) {
            if (($load['load_type'] ?? '') === 'TUE') {
                $tueIndices[] = $li;
            }
        }

        if (!empty($tueIndices)) {
            usort($tueIndices, function($a, $b) use ($getFloor) {
                $vA = (int)($this->loads[$a]['voltage_v'] ?? 127);
                $vB = (int)($this->loads[$b]['voltage_v'] ?? 127);
                if ($vA !== $vB) return $vA <=> $vB;

                $phA = (int)($this->loads[$a]['phases'] ?? 1);
                $phB = (int)($this->loads[$b]['phases'] ?? 1);
                if ($phA !== $phB) return $phA <=> $phB;

                $flA = $getFloor($this->loads[$a]);
                $flB = $getFloor($this->loads[$b]);
                if ($flA !== $flB) return $flA <=> $flB;

                return (int)($this->loads[$a]['sort_order'] ?? 0) <=> (int)($this->loads[$b]['sort_order'] ?? 0);
            });

            foreach ($tueIndices as $li) {
                $this->loads[$li]['circuit_number'] = $nextCircuit++;
            }
        }

        // Leftover loads fallback safety
        foreach (array_keys($this->loads) as $li) {
            if (($this->loads[$li]['circuit_number'] ?? null) === null || (int)$this->loads[$li]['circuit_number'] <= 0) {
                $this->loads[$li]['circuit_number'] = $nextCircuit++;
            }
        }

        $this->renumberCircuitsBySequence();
        $this->sortLoadsBySequence();

        $this->successMessage = 'Circuitos distribuídos automaticamente (Iluminação → TUG → TUE).';
        $this->dispatch('toast', type: 'success', message: 'Circuitos atribuídos automaticamente!');
    }

    public function mergeLoadParts(int $li): void
    {
        $this->clearMessages();
        if (!isset($this->loads[$li])) return;

        $load = $this->loads[$li];
        $desc = $load['description'] ?? '';

        // Extract base (strip "— pt X/Y" suffix)
        $baseDesc = preg_replace('/\s*— pt \d+\/\d+$/', '', $desc);
        if ($baseDesc === $desc) {
            $this->errorMessage = 'Esta carga não é parte de uma divisão.';
            return;
        }

        // Find all sibling parts: same room_id, same load_type, same base description
        $siblingIndices = [];
        $pattern = '/^' . preg_quote($baseDesc, '/') . '\s*— pt \d+\/\d+$/';
        foreach ($this->loads as $i => $l) {
            if (($l['room_id'] ?? null) == ($load['room_id'] ?? null)
                && ($l['load_type'] ?? '') === ($load['load_type'] ?? '')
                && preg_match($pattern, $l['description'] ?? '')) {
                $siblingIndices[] = $i;
            }
        }

        if (empty($siblingIndices)) {
            $this->errorMessage = 'Nenhuma parte encontrada para reagrupar.';
            return;
        }

        $this->pushHistory();

        sort($siblingIndices);
        $insertPos   = $siblingIndices[0];
        $firstLoad   = $this->loads[$insertPos];
        $idsToDelete = [];
        $summedVa    = 0;

        foreach ($siblingIndices as $i) {
            $s  = $this->loads[$i];
            $va = ($s['use_manual_va'] ?? false) && ($s['manual_va'] ?? '') !== ''
                ? (int)$s['manual_va']
                : (int)($s['power_va'] ?? 0);
            $summedVa += $va;
            if (!empty($s['id'])) $idsToDelete[] = $s['id'];
        }

        // Prefer stored original VA over sum of remaining parts
        $restoredVa   = ($firstLoad['split_original_va'] ?? null) !== null
            ? (int)$firstLoad['split_original_va']
            : $summedVa;
        $restoredDesc = ($firstLoad['split_original_description'] ?? null) !== null && ($firstLoad['split_original_description'] ?? '') !== ''
            ? $firstLoad['split_original_description']
            : $baseDesc;

        // Delete siblings from DB
        foreach ($idsToDelete as $id) {
            try { DB::table('project_loads')->where('id', $id)->delete(); } catch (\Throwable) {}
        }

        // Remove siblings in reverse order (preserves lower indices)
        foreach (array_reverse($siblingIndices) as $i) {
            array_splice($this->loads, $i, 1);
        }
        $this->loads = array_values($this->loads);

        // Insert restored load
        $merged = array_merge($firstLoad, [
            'id'                         => null,
            'description'                => $restoredDesc,
            'power_va'                   => $restoredVa,
            'unit_va'                    => $restoredVa,
            'is_auto'                    => false,
            'circuit_number'             => null,
            'use_manual_va'              => false,
            'manual_va'                  => '',
            'power_w'                    => null,
            'current_a'                  => null,
            'grouping_factor'            => null,
            'temperature_factor'         => null,
            'corrected_current_a'        => null,
            'split_original_va'          => null,
            'split_original_description' => null,
        ]);

        array_splice($this->loads, $insertPos, 0, [$merged]);
        $this->loads = array_values($this->loads);
        $this->calculateLoad($insertPos);

        $count = count($siblingIndices);
        $this->successMessage = "{$count} parte(s) reagrupadas → \"{$restoredDesc}\" ({$restoredVa}VA). Atribua um circuito.";
    }

    private function groupLoadsIntoCircuits(array $indices, int &$nextCircuit, float $maxCurrentA): void
    {
        $currentCircuit   = $nextCircuit++;
        $currentCircuitI  = 0.0;

        foreach ($indices as $li) {
            $load    = $this->loads[$li];
            $va      = ($load['use_manual_va'] ?? false) && ($load['manual_va'] ?? '') !== ''
                ? (int)$load['manual_va']
                : (int)($load['power_va'] ?? 0);
            $voltage = max(1, (int)($load['voltage_v'] ?? 127));
            $phases  = (int)($load['phases'] ?? 1);

            $loadI = $phases === 3
                ? $va / (sqrt(3) * $voltage)
                : $va / $voltage;

            if ($currentCircuitI > 0 && ($currentCircuitI + $loadI) > $maxCurrentA) {
                $currentCircuit  = $nextCircuit++;
                $currentCircuitI = 0.0;
            }

            $this->loads[$li]['circuit_number'] = $currentCircuit;
            $currentCircuitI += $loadI;
        }
    }

    private function loadTypeSequenceOrder(string $type): int
    {
        $type = mb_strtoupper(trim($type));

        if (str_contains($type, 'ILUM')) return 1;
        if (str_contains($type, 'TUG')) return 2;
        if (str_contains($type, 'TUE')) return 3;

        return 99;
    }

    private function renumberCircuitsBySequence(): void
    {
        $circuits = [];

        foreach ($this->loads as $li => $load) {
            $circNum = (int)($load['circuit_number'] ?? 0);
            if ($circNum <= 0) continue;

            if (!isset($circuits[$circNum])) {
                $circuits[$circNum] = [
                    'old_circuit' => $circNum,
                    'type_order'  => 99,
                    'voltage'     => PHP_INT_MAX,
                    'phases'      => PHP_INT_MAX,
                    'floor'       => PHP_INT_MAX,
                    'room'        => PHP_INT_MAX,
                    'sort'        => PHP_INT_MAX,
                ];
            }

            $circuits[$circNum]['type_order'] = min(
                $circuits[$circNum]['type_order'],
                $this->loadTypeSequenceOrder((string)($load['load_type'] ?? ''))
            );
            $circuits[$circNum]['voltage'] = min($circuits[$circNum]['voltage'], (int)($load['voltage_v'] ?? 127));
            $circuits[$circNum]['phases']  = min($circuits[$circNum]['phases'], (int)($load['phases'] ?? 1));
            $circuits[$circNum]['floor']   = min($circuits[$circNum]['floor'], (int)($load['floor_number'] ?? 1));
            $circuits[$circNum]['room']    = min($circuits[$circNum]['room'], (int)($load['room_index'] ?? $li));
            $circuits[$circNum]['sort']    = min($circuits[$circNum]['sort'], (int)($load['sort_order'] ?? 0));
        }

        if (empty($circuits)) return;

        usort($circuits, function (array $a, array $b) {
            foreach (['type_order', 'voltage', 'phases', 'floor', 'room', 'sort', 'old_circuit'] as $field) {
                if ($a[$field] !== $b[$field]) return $a[$field] <=> $b[$field];
            }

            return 0;
        });

        $circuitMap = [];
        $nextCircuit = 1;
        foreach ($circuits as $circuit) {
            $circuitMap[$circuit['old_circuit']] = $nextCircuit++;
        }

        foreach (array_keys($this->loads) as $li) {
            $oldCircuit = (int)($this->loads[$li]['circuit_number'] ?? 0);
            if ($oldCircuit > 0 && isset($circuitMap[$oldCircuit])) {
                $this->loads[$li]['circuit_number'] = $circuitMap[$oldCircuit];
            }
        }
    }

    public function splitLoad(int $li, int $count, string $vasJson): void
    {
        $this->clearMessages();
        if (!isset($this->loads[$li])) return;
        $this->pushHistory();

        if ($count < 2 || $count > 8) {
            $this->errorMessage = 'Número de partes deve ser entre 2 e 8.';
            return;
        }

        $vaList = json_decode($vasJson, true);
        if (!is_array($vaList) || count($vaList) !== $count) {
            $this->errorMessage = 'Dados de divisão inválidos.';
            return;
        }

        foreach ($vaList as $va) {
            if ((int)$va <= 0) {
                $this->errorMessage = 'Todas as parcelas devem ter VA maior que zero.';
                return;
            }
        }

        $originalLoad = $this->loads[$li];

        // Remove original from DB if saved
        if (!empty($originalLoad['id'])) {
            try { DB::table('project_loads')->where('id', $originalLoad['id'])->delete(); } catch (\Throwable) {}
        }

        // Next available circuit numbers (above current max)
        $maxCircuit = 0;
        foreach ($this->loads as $load) {
            $cn = (int)($load['circuit_number'] ?? 0);
            if ($cn > $maxCircuit) $maxCircuit = $cn;
        }
        $nextCircuit = $maxCircuit + 1;

        $baseName = mb_substr(
            $originalLoad['description'] ?: $originalLoad['load_type'],
            0, 22
        );

        $originalVa   = ($originalLoad['use_manual_va'] ?? false) && ($originalLoad['manual_va'] ?? '') !== ''
            ? (int)$originalLoad['manual_va']
            : (int)($originalLoad['power_va'] ?? 0);
        $originalDesc = $originalLoad['description'] ?? '';

        $newLoads = [];
        for ($i = 0; $i < $count; $i++) {
            $newLoads[] = array_merge($originalLoad, [
                'id'                         => null,
                'description'                => $baseName . ' — pt ' . ($i + 1) . '/' . $count,
                'power_va'                   => (int)$vaList[$i],
                'unit_va'                    => (int)$vaList[$i],
                'quantity'                   => 1,
                'is_auto'                    => false,
                'circuit_number'             => $nextCircuit + $i,
                'use_manual_va'              => false,
                'manual_va'                  => '',
                'power_w'                    => null,
                'current_a'                  => null,
                'grouping_factor'            => null,
                'temperature_factor'         => null,
                'corrected_current_a'        => null,
                'split_original_va'          => $originalVa,
                'split_original_description' => $originalDesc,
            ]);
        }

        array_splice($this->loads, $li, 1, $newLoads);
        $this->loads = array_values($this->loads);

        for ($i = $li; $i < $li + $count; $i++) {
            if (isset($this->loads[$i])) $this->calculateLoad($i);
        }

        $this->renumberCircuitsBySequence();
        $partCircuits = [];
        for ($i = $li; $i < $li + $count; $i++) {
            if (isset($this->loads[$i])) {
                $partCircuits[] = (int)($this->loads[$i]['circuit_number'] ?? 0);
            }
        }

        $this->sortLoadsBySequence();

        $partCircuits = array_values(array_unique(array_filter($partCircuits)));
        sort($partCircuits);
        $first = $partCircuits[0] ?? $nextCircuit;
        $last  = $partCircuits[count($partCircuits) - 1] ?? $first;
        $this->successMessage = "Carga dividida em {$count} partes → C{$first}" . ($last > $first ? "–C{$last}" : '') . '.';
    }

    private function validateStep4(): void
    {
        $errors = [];

        $unassigned = array_filter(
            $this->loads,
            fn($l) => ($l['circuit_number'] ?? null) === null || $l['circuit_number'] === '' || (int)($l['circuit_number'] ?? 0) <= 0
        );
        if (!empty($unassigned)) {
            $errors['step4.unassigned'] = count($unassigned) . ' carga(s) sem circuito atribuído. Atribua todos os circuitos antes de avançar.';
        }

        $previews = $this->getCircuitPreviews();
        foreach ($previews as $preview) {
            if ($preview['has_critical_error']) {
                $errors['step4.critical'] = 'Existem erros críticos nos circuitos (tensão/fases mistos). Corrija antes de avançar.';
                break;
            }
        }

        if (!empty($errors)) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    public function saveStep4(): void
    {
        if (!$this->projectId) return;

        $this->renumberCircuitsBySequence();
        $this->sortLoadsBySequence();

        // Persist circuit_number to project_loads
        foreach ($this->loads as $load) {
            if (empty($load['id'])) continue;
            try {
                $circNum = ($load['circuit_number'] ?? null) !== null && (int)($load['circuit_number'] ?? 0) > 0
                    ? (int)$load['circuit_number']
                    : null;
                DB::table('project_loads')
                    ->where('id', $load['id'])
                    ->update(['circuit_number' => $circNum, 'updated_at' => now()]);
            } catch (\Throwable) {}
        }

        // Save circuit previews
        $previews = $this->getCircuitPreviews();
        try {
            DB::table('project_circuit_previews')->where('project_id', $this->projectId)->delete();
            foreach ($previews as $preview) {
                DB::table('project_circuit_previews')->insert([
                    'project_id'        => $this->projectId,
                    'circuit_number'    => $preview['circuit_number'],
                    'circuit_type'      => $preview['circuit_type'],
                    'total_loads'       => $preview['total_loads'],
                    'total_power_va'    => $preview['total_power_va'],
                    'total_power_w'     => $preview['total_power_w'],
                    'resulting_fp'      => $preview['resulting_fp'],
                    'voltage_v'         => $preview['voltage_v'],
                    'phases'            => $preview['phases'],
                    'current_a'         => $preview['current_a'],
                    'status'            => $preview['status'],
                    'alerts_json'       => json_encode($preview['alerts']),
                    'has_critical_error'=> $preview['has_critical_error'] ? 1 : 0,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]);
            }
        } catch (\Throwable) {}

        // Update project_input_rows to use the actual circuit assignments
        $this->mirrorLoadsToInputRows();
    }

    // ─── Legacy Step 3 helpers (kept for compat) ─────────

    public function suggestLoads(int $index): void
    {
        if (!isset($this->rooms[$index])) return;
        $room     = &$this->rooms[$index];
        $area     = (float)($room['area_m2']     ?? 0);
        $perim    = (float)($room['perimeter_m'] ?? 0);
        $roomType = $room['room_type'] ?? '';

        $minLighting = $this->calcMinLightingVa($area);
        $tugData     = $this->calcMinTugData($roomType, $area, $perim);

        $room['min_lighting_va'] = $minLighting;
        $room['min_tug_qty']     = $tugData['qty'];
        $room['tug_unit_va']     = $tugData['unit_va'];
        $room['tug_unit_va_min'] = $tugData['unit_va'];

        if ($room['lighting_va'] === '' || (float)$room['lighting_va'] < $minLighting) {
            $room['lighting_va']  = (string)$minLighting;
            $room['has_lighting'] = true;
        }
        if ($room['tug_qty'] === '' || (int)$room['tug_qty'] < $tugData['qty']) {
            $room['tug_qty'] = (string)$tugData['qty'];
            $room['has_tug'] = true;
        }

        $this->revalidateRoom($index);
        $room['suggestion_applied'] = true;
    }

    public function revalidateRoom(int $index): void
    {
        if (!isset($this->rooms[$index])) return;
        $room = &$this->rooms[$index];

        $minLighting = (float)($room['min_lighting_va'] ?? 0);
        $minTugQty   = (int)($room['min_tug_qty']       ?? 0);

        $room['lighting_below_min'] = $minLighting > 0 && (float)($room['lighting_va'] ?? 0) < $minLighting;
        $room['tug_below_min']      = $minTugQty   > 0 && (int)($room['tug_qty']       ?? 0) < $minTugQty;
    }

    public function addTue(int $roomIndex): void
    {
        if (!isset($this->rooms[$roomIndex])) return;
        $this->rooms[$roomIndex]['tues'][] = [
            'id'          => null,
            'description' => '',
            'power_va'    => '',
            'phases'      => '',
            'voltage'     => '',
            'distance_m'  => '',
        ];
    }

    public function removeTue(int $roomIndex, int $tueIndex): void
    {
        if (!isset($this->rooms[$roomIndex]['tues'][$tueIndex])) return;
        $tue = $this->rooms[$roomIndex]['tues'][$tueIndex];
        if (!empty($tue['id'])) {
            ProjectInputRow::destroy($tue['id']);
        }
        array_splice($this->rooms[$roomIndex]['tues'], $tueIndex, 1);
        $this->rooms[$roomIndex]['tues'] = array_values($this->rooms[$roomIndex]['tues']);
    }

    // ─── Utilitários ─────────────────────────────────────

    private function emptyRoom(int $sortOrder): array
    {
        return [
            'id'                          => null,
            'room_type'                   => '',
            'description'                 => '',
            'area_m2'                     => '',
            'perimeter_m'                 => '',
            'sort_order'                  => $sortOrder,
            'floor_number'                => 1,
            // Step 2
            'lighting_va_calculated'      => null,
            'lighting_rule_description'   => '',
            'tug_rule_group'              => '',
            'tug_qty_calculated'          => null,
            'tug_va_calculated'           => null,
            'tug_rule_description'        => '',
            'total_minimum_va_calculated' => null,
            'total_minimum_va_final'      => null,
            'use_manual_lighting'         => false,
            'lighting_va_manual'          => '',
            'use_manual_tug_qty'          => false,
            'tug_qty_manual'              => '',
            'tug_qty_600_calculated'      => null,
            'tug_qty_100_calculated'      => null,
            'tug_qty_600_manual'          => '',
            'tug_qty_100_manual'          => '',
            'use_manual_tug_va'           => false,
            'tug_va_manual'               => '',
            // Legacy
            'suggestion_applied'   => false,
            'has_lighting'         => true,
            'lighting_va'          => '',
            'lighting_row_id'      => null,
            'min_lighting_va'      => null,
            'lighting_below_min'   => false,
            'has_tug'              => true,
            'tug_qty'              => '',
            'tug_unit_va'          => 100,
            'tug_unit_va_min'      => 100,
            'tug_row_id'           => null,
            'min_tug_qty'          => null,
            'tug_below_min'        => false,
            'phases'               => '',
            'voltage'              => '',
            'installation_method'  => '',
            'temperature_c'        => '',
            'distance_m'           => '',
            'voltage_drop_percent' => '',
            'grouped_circuits'     => '',
            'tues'                 => [],
        ];
    }

    // ─── Undo ─────────────────────────────────────────────

    private function pushHistory(): void
    {
        $this->history[] = $this->loads;
        if (count($this->history) > 5) {
            array_shift($this->history);
        }
    }

    public function undo(): void
    {
        $this->clearMessages();
        if (empty($this->history)) {
            $this->errorMessage = 'Nenhuma ação para desfazer.';
            return;
        }
        $this->loads = array_pop($this->history);
        $this->normalizeLoads();
        $this->successMessage = 'Ação desfeita.';
    }

    // ─── Utilitários ─────────────────────────────────────

    private function clearMessages(): void
    {
        $this->successMessage = '';
        $this->errorMessage   = '';
        $this->resetValidation();
    }

    public function getProgressPercent(): float
    {
        return round(($this->currentStep / self::TOTAL_STEPS) * 100);
    }

    public function render()
    {
        return view('livewire.student.wizard.project-wizard');
    }
}
