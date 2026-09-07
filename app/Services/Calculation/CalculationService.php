<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Throwable;

class CalculationService
{
    public function __construct(
        private readonly LoadCalculationService    $loadCalculationService,
        private readonly CircuitCalculationService $circuitCalculationService,
        private readonly DemandService             $demandService,
        private readonly GroupingFactorService     $groupingFactorService,
        private readonly TemperatureFactorService  $temperatureFactorService,
        private readonly ConductorService          $conductorService,
        private readonly BreakerService            $breakerService,
        private readonly PhaseDistributionService  $phaseDistributionService,
        private readonly ConduitService            $conduitService,
        private readonly DpsService                $dpsService,
        private readonly IdrService                $idrService,
        private readonly ServiceEntranceService    $serviceEntranceService,
        private readonly GeneralCircuitService     $generalCircuitService,
    ) {}

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Executa todos os cálculos do projeto em sequência.
     *
     * Etapas:
     *   1. calculateLoads()    - calcula project_load_rows
     *   2. calculateCircuits() - consolida project_circuit_calculations
     *   3. Atualiza progress do projeto
     *
     * @param  int   $projectId ID do projeto
     * @return array ['success' => bool, 'messages' => array]
     */
    public function calculateProject(int $projectId): array
    {
        $messages = [];

        try {
            $this->calculateLoads($projectId);
            $messages[] = 'Cargas calculadas com sucesso.';
        } catch (Throwable $e) {
            return [
                'success'  => false,
                'messages' => ['Erro ao calcular cargas: ' . $e->getMessage()],
            ];
        }

        try {
            $this->calculateCircuits($projectId);
            $messages[] = 'Circuitos consolidados com sucesso.';
        } catch (Throwable $e) {
            return [
                'success'  => false,
                'messages' => array_merge($messages, ['Erro ao calcular circuitos: ' . $e->getMessage()]),
            ];
        }

        // Atualiza progresso do projeto
        try {
            DB::table('projects')
                ->where('id', $projectId)
                ->update([
                    'calculation_progress' => 'completed',
                    'calculated_at'        => now(),
                    'updated_at'           => now(),
                ]);
            $messages[] = 'Progresso do projeto atualizado.';
        } catch (Throwable $e) {
            $messages[] = 'Aviso: não foi possível atualizar o progresso do projeto: ' . $e->getMessage();
        }

        return [
            'success'  => true,
            'messages' => $messages,
        ];
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula as cargas de cada linha de entrada e salva/atualiza project_load_rows.
     *
     * Para cada project_input_row do projeto, usa LoadCalculationService para calcular
     * e salva/atualiza o resultado em project_load_rows.
     *
     * @param  int  $projectId ID do projeto
     * @return void
     */
    public function calculateLoads(int $projectId): void
    {
        DB::table('project_load_rows')
            ->where('project_id', $projectId)
            ->delete();

        $inputRows = DB::table('project_input_rows')
            ->where('project_id', $projectId)
            ->orderBy('id')
            ->get()
            ->toArray();

        foreach ($inputRows as $inputRow) {
            $inputRowArray = (array) $inputRow;

            $result = $this->loadCalculationService->calculateInputRow($inputRowArray);

            $existingLoadRow = DB::table('project_load_rows')
                ->where('project_id', $projectId)
                ->where('input_row_id', $inputRow->id)
                ->first();

            $data = [
                'project_id'      => $projectId,
                'input_row_id'    => $inputRow->id,
                'circuit_number'  => $inputRow->circuit_number ?? null,
                'room_description' => $inputRow->description ?? null,
                'lighting_va'     => $result['lighting_va'],
                'outlet_100_qty'  => $result['outlet_100_qty'],
                'outlet_600_qty'  => $result['outlet_600_qty'],
                'outlet_1000_qty' => $result['outlet_1000_qty'],
                'tue_va'          => $result['tue_va'],
                'power_factor'    => $result['power_factor'],
                'power_w'         => $result['power_w'],
                'power_va'        => $result['power_va'],
                'project_current_a' => $result['project_current_a'],
                'error_message'   => $result['error_message'],
                'updated_at'      => now(),
            ];

            if ($existingLoadRow) {
                DB::table('project_load_rows')
                    ->where('id', $existingLoadRow->id)
                    ->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('project_load_rows')->insert($data);
            }
        }
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Agrupa load_rows por circuit_number e consolida cada circuito.
     * Usa CircuitCalculationService, DemandService, GroupingFactorService,
     * TemperatureFactorService, ConductorService e BreakerService.
     * Salva/atualiza project_circuit_calculations.
     *
     * @param  int  $projectId ID do projeto
     * @return void
     */
    public function calculateCircuits(int $projectId): void
    {
        DB::table('project_circuit_calculations')
            ->where('project_id', $projectId)
            ->delete();

        // Carrega load_rows com dados de input_rows via join
        $loadRows = DB::table('project_load_rows as lr')
            ->join('project_input_rows as ir', 'ir.id', '=', 'lr.input_row_id')
            ->where('lr.project_id', $projectId)
            ->select('lr.*',
                'ir.phases',
                'ir.voltage',
                'ir.installation_method',
                'ir.temperature_c',
                'ir.distance_m',
                'ir.voltage_drop_percent',
                'ir.grouped_circuits',
                'ir.description as ir_room_description',
            )
            ->orderBy('lr.id')
            ->get()
            ->toArray();

        // Agrupa por circuit_number
        $grouped = [];
        foreach ($loadRows as $row) {
            $circuitNumber             = (int) ($row->circuit_number ?? 0);
            $grouped[$circuitNumber][] = (array) $row;
        }

        foreach ($grouped as $circuitNumber => $rows) {
            // Separa load_rows e input_rows para o consolidador
            $loadRowArrays  = $rows;
            $inputRowArrays = $rows; // contém os campos de input_rows via join

            $consolidated = $this->circuitCalculationService->consolidateCircuit(
                $circuitNumber,
                $loadRowArrays,
                $inputRowArrays
            );

            // Fatores de correção
            $temperatureC   = (int) ($consolidated['temperature_c'] ?? 30);
            $temperatureFactor = $this->temperatureFactorService->getFactor($temperatureC) ?? 1.0;

            $groupedCircuits = (int) ($consolidated['grouped_circuits'] ?? 1);
            $groupingFactor  = $this->groupingFactorService->getFactor($groupedCircuits) ?? 1.0;

            // Corrente corrigida = project_current_a / (temperature_factor * grouping_factor)
            $correctionFactor  = $temperatureFactor * $groupingFactor;
            $projectCurrentA   = (float) ($consolidated['project_current_a'] ?? 0.0);
            $correctedCurrentA = $correctionFactor > 0.0
                ? $projectCurrentA / $correctionFactor
                : $projectCurrentA;

            $phases             = is_numeric($consolidated['phases']) ? (int) $consolidated['phases'] : 1;
            $voltage            = is_numeric($consolidated['voltage']) ? (int) $consolidated['voltage'] : 0;
            $installationMethod = (string) ($consolidated['installation_method'] ?? '');
            $distanceM          = (float) ($consolidated['distance_m'] ?? 0.0);
            $voltageDropPercent = (float) ($consolidated['voltage_drop_percent'] ?? 7.0);

            // Cabo por ampacidade
            $ampacityConductor = $this->conductorService->getConductorByAmpacity(
                $correctedCurrentA,
                $installationMethod,
                $phases
            );

            // Cabo mínimo
            $minConductor = $this->conductorService->getMinimumConductor(
                (string) ($consolidated['circuit_type'] ?? '')
            );

            // Cabo por queda de tensão
            $voltageDropConductor = $this->conductorService->getConductorByVoltageDrop(
                $phases,
                $distanceM,
                $voltageDropPercent,
                $projectCurrentA,
                $voltage
            );

            // Cabo final
            $finalConductor = $this->conductorService->getFinalConductor(
                $minConductor,
                $ampacityConductor,
                $voltageDropConductor
            );

            // Iz
            $izA = $this->conductorService->getIz($finalConductor, $installationMethod, $phases);

            // Disjuntor
            $breakerA = null;
            if ($izA !== null) {
                $breakerA = $this->breakerService->selectBreaker($projectCurrentA, $izA);
            }

            // Demanda
            $powerVa       = (float) ($consolidated['power_va'] ?? 0.0);
            $powerKva      = $powerVa / 1000.0;
            $demandFactor  = $this->demandService->calculateDemandFactor($powerKva);
            $demandVa      = $this->demandService->calculateDemandVa($powerVa, $demandFactor);

            // Persiste/atualiza project_circuit_calculations
            $existing = DB::table('project_circuit_calculations')
                ->where('project_id', $projectId)
                ->where('circuit_number', $circuitNumber)
                ->first();

            $data = [
                'project_id'                    => $projectId,
                'circuit_number'                => $circuitNumber,
                'description'                   => $consolidated['description']          ?? null,
                'circuit_type'                  => $consolidated['circuit_type']         ?? null,
                'lighting_va'                   => $consolidated['lighting_va']          ?? 0,
                'outlet_100_qty'                => $consolidated['outlet_100_qty']       ?? 0,
                'outlet_600_qty'                => $consolidated['outlet_600_qty']       ?? 0,
                'outlet_1000_qty'               => $consolidated['outlet_1000_qty']      ?? 0,
                'tue_va'                        => $consolidated['tue_va']               ?? 0,
                'power_w'                       => $consolidated['power_w']              ?? 0,
                'power_va'                      => $consolidated['power_va']             ?? 0,
                'power_factor'                  => $consolidated['power_factor']         ?? 1,
                'phases'                        => $consolidated['phases']               ?? null,
                'voltage'                       => $consolidated['voltage']              ?? null,
                'installation_method'           => $consolidated['installation_method']  ?? null,
                'temperature_c'                 => $consolidated['temperature_c']        ?? null,
                'distance_m'                    => $consolidated['distance_m']           ?? null,
                'voltage_drop_percent'          => $consolidated['voltage_drop_percent'] ?? null,
                'grouped_circuits'              => $consolidated['grouped_circuits']     ?? null,
                'project_current_a'             => $consolidated['project_current_a']   ?? 0,
                'temperature_factor'            => $temperatureFactor,
                'grouping_factor'               => $groupingFactor,
                'corrected_current_a'           => $correctedCurrentA,
                'min_conductor_mm2'             => $minConductor,
                'ampacity_conductor_mm2'        => $ampacityConductor,
                'voltage_drop_conductor_mm2'    => $voltageDropConductor,
                'final_conductor_calculated_mm2'=> $finalConductor,
                'iz_a'                          => $izA,
                'breaker_calculated_a'          => $breakerA,
                'demand_factor_calculated'      => $demandFactor,
                'demand_va_calculated'          => $demandVa,
                'calc_status'                   => $consolidated['has_errors'] ? 'error' : 'ok',
                'notes'                         => !empty($consolidated['error_messages']) ? json_encode($consolidated['error_messages']) : null,
                'updated_at'                    => now(),
            ];

            if ($existing) {
                DB::table('project_circuit_calculations')
                    ->where('id', $existing->id)
                    ->update($data);
            } else {
                $data['created_at'] = now();
                DB::table('project_circuit_calculations')->insert($data);
            }
        }
    }
}
