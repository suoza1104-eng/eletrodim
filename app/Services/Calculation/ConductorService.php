<?php

declare(strict_types=1);

namespace App\Services\Calculation;

use Illuminate\Support\Facades\DB;

class ConductorService
{
    /** Bitolas padronizadas de cabos em mm² */
    private const STANDARD_CONDUCTORS = [1.5, 2.5, 4.0, 6.0, 10.0, 16.0, 25.0, 35.0, 50.0, 70.0, 95.0];

    /** Resistividade do cobre (Ω·mm²/m) */
    private const RHO_COPPER = 0.0178;

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna cabo mínimo conforme tipo de circuito.
     * 'ILUMINAÇÃO' => 1.5 mm²; demais => 2.5 mm²
     *
     * @param  string $circuitType Tipo de circuito
     * @return float  Seção mínima em mm²
     */
    public function getMinimumConductor(string $circuitType): float
    {
        if (mb_strtoupper(trim($circuitType)) === 'ILUMINAÇÃO') {
            return 1.5;
        }

        return 2.5;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna menor cabo por ampacidade buscando na tabela conductor_ampacity.
     * group = '1_2' se fases 1 ou 2; '3' se fases 3.
     * Busca menor cabo onde iz_amperes >= correctedCurrentA.
     *
     * @param  float       $correctedCurrentA Corrente corrigida em amperes
     * @param  string      $method            Método de instalação
     * @param  int         $phases            Quantidade de fases
     * @return float|null  Seção do cabo em mm², ou null se não encontrar
     */
    public function getConductorByAmpacity(float $correctedCurrentA, string $method, int $phases): ?float
    {
        $group = ($phases === 3) ? '3' : '1_2';

        $record = DB::table('conductor_ampacity')
            ->where('installation_method', $method)
            ->where('phase_group', $group)
            ->where('iz_amperes', '>=', $correctedCurrentA)
            ->orderBy('conductor_mm2', 'asc')
            ->first();

        if ($record === null) {
            return null;
        }

        return (float) $record->conductor_mm2;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna corrente Iz do cabo escolhido buscando na tabela conductor_ampacity.
     *
     * @param  float       $conductorMm2 Seção do cabo em mm²
     * @param  string      $method       Método de instalação
     * @param  int         $phases       Quantidade de fases
     * @return float|null  Corrente Iz em amperes, ou null se não encontrar
     */
    public function getIz(float $conductorMm2, string $method, int $phases): ?float
    {
        $group = ($phases === 3) ? '3' : '1_2';

        $record = DB::table('conductor_ampacity')
            ->where('installation_method', $method)
            ->where('phase_group', $group)
            ->where('conductor_mm2', $conductorMm2)
            ->first();

        if ($record === null) {
            return null;
        }

        return (float) $record->iz_amperes;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna menor cabo por queda de tensão.
     *
     * Fórmulas:
     *   Monofásico/bifásico: S = (2 * rho * L * I * 100) / (queda_percent * V)
     *   Trifásico:           S = (1.73 * rho * L * I * 100) / (queda_percent * V)
     *   rho = 0.0178 (cobre)
     *
     * Retorna menor cabo padronizado >= S calculado.
     *
     * @param  int         $phases              Quantidade de fases
     * @param  float       $distanceM           Comprimento do circuito em metros
     * @param  float       $voltageDropPercent  Queda de tensão máxima permitida em %
     * @param  float       $currentA            Corrente em amperes
     * @param  int         $voltage             Tensão em volts
     * @return float|null  Seção do cabo em mm², ou null se voltage <= 0 ou queda <= 0
     */
    public function getConductorByVoltageDrop(
        int $phases,
        float $distanceM,
        float $voltageDropPercent,
        float $currentA,
        int $voltage
    ): ?float {
        if ($voltage <= 0 || $voltageDropPercent <= 0.0) {
            return null;
        }

        if ($phases === 3) {
            $sectionMm2 = (1.73 * self::RHO_COPPER * $distanceM * $currentA * 100.0)
                / ($voltageDropPercent * $voltage);
        } else {
            $sectionMm2 = (2.0 * self::RHO_COPPER * $distanceM * $currentA * 100.0)
                / ($voltageDropPercent * $voltage);
        }

        foreach (self::STANDARD_CONDUCTORS as $standardSection) {
            if ($standardSection >= $sectionMm2) {
                return $standardSection;
            }
        }

        // Maior cabo padronizado disponível
        return end(self::STANDARD_CONDUCTORS) ?: null;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna cabo final = max(min, ampacity, voltage_drop).
     *
     * @param  float       $minConductor          Seção mínima em mm²
     * @param  float|null  $ampacityConductor     Seção por ampacidade em mm²
     * @param  float|null  $voltageDropConductor  Seção por queda de tensão em mm²
     * @return float       Maior entre os três valores
     */
    public function getFinalConductor(
        float $minConductor,
        ?float $ampacityConductor,
        ?float $voltageDropConductor
    ): float {
        $values = [$minConductor];

        if ($ampacityConductor !== null) {
            $values[] = $ampacityConductor;
        }

        if ($voltageDropConductor !== null) {
            $values[] = $voltageDropConductor;
        }

        return max($values);
    }
}
