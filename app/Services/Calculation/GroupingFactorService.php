<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class GroupingFactorService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Tabela de fatores de agrupamento conforme quantidade de circuitos agrupados.
     * Chave = quantidade de circuitos; valor = fator.
     */
    private const GROUPING_FACTOR_TABLE = [
        1  => 1.00,
        2  => 0.80,
        3  => 0.70,
        4  => 0.65,
        5  => 0.60,
        6  => 0.57,
        7  => 0.54,
        8  => 0.52,
    ];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna fator de agrupamento conforme quantidade de circuitos agrupados.
     *
     * Faixas:  9–11  => 0.50
     *         12–15  => 0.45
     *         16–19  => 0.41
     *         >= 20  => 0.38
     *
     * @param  int        $groupedCircuits Quantidade de circuitos agrupados
     * @return float|null Fator de agrupamento, ou null se valor inválido (<= 0)
     */
    public function getFactor(int $groupedCircuits): ?float
    {
        if ($groupedCircuits <= 0) {
            return null;
        }

        if (isset(self::GROUPING_FACTOR_TABLE[$groupedCircuits])) {
            return self::GROUPING_FACTOR_TABLE[$groupedCircuits];
        }

        if ($groupedCircuits <= 11) {
            return 0.50;
        }

        if ($groupedCircuits <= 15) {
            return 0.45;
        }

        if ($groupedCircuits <= 19) {
            return 0.41;
        }

        return 0.38;
    }
}
