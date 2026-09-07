<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class IdrService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Correntes nominais disponíveis para IDR em amperes.
     */
    private const IDR_CURRENTS = [25, 40, 63, 80, 100, 125];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Corrente residual padrão.
     */
    private const DEFAULT_RESIDUAL_CURRENT = '30 mA';

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Tabela de ICC padronizado em kA.
     */
    private const ICC_TABLE = [5, 10, 20, 30, 45, 65];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna especificações do IDR.
     *
     * IDR In: menor valor da lista [25, 40, 63, 80, 100, 125] >= breakerInA
     *
     * Polos conforme regras:
     *   - phases == 1 e hasNeutral == 'SIM' => 2 polos
     *   - phases == 1 e hasNeutral == 'NÃO' => 1 polo
     *   - phases == 2 e hasNeutral == 'SIM' => 3 polos
     *   - phases == 2 e hasNeutral == 'NÃO' => 2 polos
     *   - phases == 3 e hasNeutral == 'SIM' => 4 polos
     *   - phases == 3 e hasNeutral == 'NÃO' => 3 polos
     *
     * Corrente residual padrão: '30 mA'
     * ICC: menor valor padronizado >= shortCircuitKa em tabela [5, 10, 20, 30, 45, 65]
     *
     * @param  int    $phases          Quantidade de fases
     * @param  string $hasNeutral      'SIM' ou 'NÃO'
     * @param  float  $shortCircuitKa  Corrente de curto-circuito em kA
     * @param  int    $breakerInA      Corrente nominal do disjuntor em amperes
     * @return array  ['nominal_current_a' => int|null, 'poles' => int, 'residual_current' => string,
     *                 'icc' => int, 'idr_status' => string]
     */
    public function calculate(int $phases, string $hasNeutral, float $shortCircuitKa, int $breakerInA): array
    {
        // Corrente nominal do IDR
        $nominalCurrentA = $this->resolveIdrCurrent($breakerInA);

        // Polos
        $poles = $this->resolvePoles($phases, $hasNeutral);

        // Corrente residual
        $residualCurrent = self::DEFAULT_RESIDUAL_CURRENT;

        // ICC padronizado
        $icc = $this->resolveIcc($shortCircuitKa);

        // Status
        $idrStatus = $nominalCurrentA !== null ? 'OK' : 'CORRENTE_INDISPONÍVEL';

        return [
            'nominal_current_a' => $nominalCurrentA,
            'poles'             => $poles,
            'residual_current'  => $residualCurrent,
            'icc'               => $icc,
            'idr_status'        => $idrStatus,
        ];
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna menor corrente IDR disponível >= breakerInA, ou null se não encontrar.
     */
    private function resolveIdrCurrent(int $breakerInA): ?int
    {
        foreach (self::IDR_CURRENTS as $current) {
            if ($current >= $breakerInA) {
                return $current;
            }
        }

        return null;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Resolve quantidade de polos do IDR conforme fases e presença de neutro.
     */
    private function resolvePoles(int $phases, string $hasNeutral): int
    {
        $neutral = mb_strtoupper(trim($hasNeutral)) === 'SIM';

        return match ($phases) {
            1 => $neutral ? 2 : 1,
            2 => $neutral ? 3 : 2,
            3 => $neutral ? 4 : 3,
            default => 2,
        };
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna menor ICC padronizado >= shortCircuitKa (em kA).
     */
    private function resolveIcc(float $shortCircuitKa): int
    {
        foreach (self::ICC_TABLE as $value) {
            if ($value >= $shortCircuitKa) {
                return $value;
            }
        }

        return 65;
    }
}
