<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class GeneralCircuitService
{
    public function __construct(
        private readonly ConductorService $conductorService,
        private readonly BreakerService   $breakerService,
    ) {}

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula o circuito de distribuição geral (aba DG da planilha).
     *
     * @param  array $dgData              Dados do DG: phases, voltage, installation_method,
     *                                    length_m, voltage_drop_percent, power_factor
     * @param  array $circuitCalculations Array de resultados consolidados de todos os circuitos
     * @return array ['demand_va' => float, 'project_current_a' => float, 'min_conductor' => float,
     *                'ampacity_conductor' => float|null, 'voltage_drop_conductor' => float|null,
     *                'final_conductor' => float, 'iz_a' => float|null, 'breaker_a' => int|null]
     */
    public function calculate(array $dgData, array $circuitCalculations): array
    {
        $phases             = (int)   ($dgData['phases']               ?? 1);
        $voltage            = (int)   ($dgData['voltage']              ?? 0);
        $installationMethod = (string) ($dgData['installation_method'] ?? '');
        $lengthM            = (float) ($dgData['length_m']             ?? 0.0);
        $voltageDropPercent = (float) ($dgData['voltage_drop_percent'] ?? 7.0);
        $powerFactor        = (float) ($dgData['power_factor']         ?? 1.0);

        // Soma das demandas de todos os circuitos
        $demandVa = 0.0;
        foreach ($circuitCalculations as $circuit) {
            $demandVa += (float) ($circuit['demand_va'] ?? $circuit['power_va'] ?? 0.0);
        }

        // Corrente de projeto
        $projectCurrentA = 0.0;
        if ($voltage > 0) {
            if ($phases === 3) {
                $projectCurrentA = $demandVa / (1.73 * $voltage);
            } else {
                $projectCurrentA = $demandVa / $voltage;
            }
        }

        // Cabo mínimo (DG é sempre circuito de alimentação, não iluminação)
        $minConductor = $this->conductorService->getMinimumConductor('TUG');

        // Corrente corrigida (sem fatores de agrupamento/temperatura no DG por padrão)
        // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
        $correctedCurrentA = $projectCurrentA;

        // Cabo por ampacidade
        $ampacityConductor = $this->conductorService->getConductorByAmpacity(
            $correctedCurrentA,
            $installationMethod,
            $phases
        );

        // Cabo por queda de tensão
        $voltageDropConductor = $this->conductorService->getConductorByVoltageDrop(
            $phases,
            $lengthM,
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

        // Iz do cabo final
        $izA = $this->conductorService->getIz($finalConductor, $installationMethod, $phases);

        // Disjuntor
        $breakerA = null;
        if ($izA !== null) {
            $breakerA = $this->breakerService->selectBreaker($projectCurrentA, $izA);
        }

        return [
            'demand_va'             => $demandVa,
            'project_current_a'     => $projectCurrentA,
            'min_conductor'         => $minConductor,
            'ampacity_conductor'    => $ampacityConductor,
            'voltage_drop_conductor' => $voltageDropConductor,
            'final_conductor'       => $finalConductor,
            'iz_a'                  => $izA,
            'breaker_a'             => $breakerA,
        ];
    }
}
