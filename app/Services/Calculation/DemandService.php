<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class DemandService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Tabela de fatores de demanda conforme NBR.
     * Chave = limite superior do intervalo em kVA (float); valor = fator de demanda.
     * Ordem: da menor para a maior potência.
     */
    private const DEMAND_FACTOR_TABLE = [
        1.0  => 0.86,
        2.0  => 0.81,
        3.0  => 0.76,
        4.0  => 0.72,
        5.0  => 0.69,
        6.0  => 0.66,
        7.0  => 0.63,
        8.0  => 0.61,
        9.0  => 0.59,
        10.0 => 0.57,
    ];

    /** Fator de demanda para potências acima de 10 kVA */
    private const DEMAND_FACTOR_ABOVE_10 = 0.45;

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula fator de demanda conforme tabela NBR.
     *
     * @param  float  $powerKva Potência acumulada em kVA dos circuitos não-TUE
     * @return float|null  Fator de demanda, ou null se $powerKva == 0 (TUE - especificar manualmente)
     */
    public function calculateDemandFactor(float $powerKva): ?float
    {
        if ($powerKva <= 0.0) {
            return null;
        }

        foreach (self::DEMAND_FACTOR_TABLE as $limit => $factor) {
            if ($powerKva <= $limit) {
                return $factor;
            }
        }

        return self::DEMAND_FACTOR_ABOVE_10;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula demanda provável em VA.
     *
     * @param  float       $powerVa      Potência instalada em VA
     * @param  float|null  $demandFactor Fator de demanda (null => demanda não calculada)
     * @return float|null  Demanda provável em VA, ou null se fator for null
     */
    public function calculateDemandVa(float $powerVa, ?float $demandFactor): ?float
    {
        if ($demandFactor === null) {
            return null;
        }

        return $powerVa * $demandFactor;
    }
}
