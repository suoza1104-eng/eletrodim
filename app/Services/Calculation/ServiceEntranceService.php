<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use Illuminate\Support\Facades\DB;

class ServiceEntranceService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Busca regra de padrão de entrada conforme demanda, fases e posição do poste.
     *
     * Consulta a tabela service_entrance_rules filtrando por:
     *   - faixas de demanda que contemplam demandKva
     *   - quantidade de fases
     *   - posição do poste (opcional)
     *
     * Se não encontrar regra: retorna array com campos básicos e nota de que precisa configuração.
     *
     * @param  float       $demandKva    Demanda provável em kVA
     * @param  int         $phases       Quantidade de fases
     * @param  string|null $polePosition Posição do poste (opcional)
     * @return array       Todos os campos da tabela service_entrance_rules, ou campos básicos com nota
     */
    public function calculate(float $demandKva, int $phases, ?string $polePosition = null): array
    {
        $query = DB::table('service_entrance_rules')
            ->where('phases', $phases)
            ->where('demand_min_kva', '<=', $demandKva)
            ->where('demand_max_kva', '>=', $demandKva);

        if ($polePosition !== null) {
            $query->where('pole_position', $polePosition);
        }

        $rule = $query->orderBy('demand_min_kva', 'desc')->first();

        if ($rule === null) {
            return [
                'phases'       => $phases,
                'demand_kva'   => $demandKva,
                'pole_position' => $polePosition,
                'notes'        => 'Nenhuma regra de entrada encontrada para os parâmetros informados. Configure a tabela service_entrance_rules.',
            ];
        }

        return (array) $rule;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula carga instalada e demanda provável a partir dos circuitos.
     *
     * @param  array $circuitCalculations Array de resultados de CircuitCalculationService::consolidateCircuit()
     * @return array ['installed_load_kw' => float, 'probable_demand_kva' => float]
     */
    public function calculateTotals(array $circuitCalculations): array
    {
        $installedLoadW  = 0.0;
        $probableDemandVa = 0.0;

        foreach ($circuitCalculations as $circuit) {
            $installedLoadW   += (float) ($circuit['power_w']  ?? 0.0);
            $probableDemandVa += (float) ($circuit['demand_va'] ?? $circuit['power_va'] ?? 0.0);
        }

        return [
            'installed_load_kw'   => round($installedLoadW / 1000.0, 4),
            'probable_demand_kva' => round($probableDemandVa / 1000.0, 4),
        ];
    }
}
