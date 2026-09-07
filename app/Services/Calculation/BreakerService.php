<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class BreakerService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Lista padrão de disjuntores disponíveis em amperes.
     */
    private const STANDARD_BREAKERS = [2, 4, 6, 10, 16, 20, 25, 32, 40, 50, 63, 80, 100];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Seleciona menor disjuntor onde: In >= project_current_a AND In <= iz_a.
     *
     * @param  float    $projectCurrentA Corrente de projeto em amperes
     * @param  float    $izA             Corrente de ampacidade do cabo em amperes
     * @return int|null Corrente nominal do disjuntor em amperes, ou null se não encontrar
     */
    public function selectBreaker(float $projectCurrentA, float $izA): ?int
    {
        foreach (self::STANDARD_BREAKERS as $breakerA) {
            if ($breakerA >= $projectCurrentA && $breakerA <= $izA) {
                return $breakerA;
            }
        }

        return null;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Valida se o disjuntor escolhido é compatível com a corrente de projeto e ampacidade.
     *
     * @param  int   $breakerA        Corrente nominal do disjuntor em amperes
     * @param  float $projectCurrentA Corrente de projeto em amperes
     * @param  float $izA             Corrente de ampacidade do cabo em amperes
     * @return bool  true se o disjuntor for compatível
     */
    public function validateBreaker(int $breakerA, float $projectCurrentA, float $izA): bool
    {
        return $breakerA >= $projectCurrentA && $breakerA <= $izA;
    }
}
