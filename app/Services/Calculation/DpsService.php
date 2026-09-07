<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class DpsService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Tensão de proteção máxima (Up) padrão conforme classe do DPS em kV.
     * Chave = classe ('I', 'II', 'III'); valor = Up em kV.
     */
    private const UP_BY_CLASS = [
        'I'   => 4.0,
        'II'  => 2.5,
        'III' => 1.5,
    ];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula especificações do DPS conforme localização.
     *
     * Regras:
     *   - 'Descargas diretas'              => Classe I  (SPD tipo 1)
     *   - 'Descargas Indiretas'            => Classe II (SPD tipo 2)
     *   - 'Proteção Individual Complementar' => Classe III (SPD tipo 3)
     *
     * Tensão de continuidade (Uc):
     *   - 127 V => Uc = 150 V; 220 V => Uc = 275 V; 380 V => Uc = 420 V; demais => tensão * 1.1
     *
     * Corrente de impulso (In) e impulso máximo (Iimp):
     *   - Classe I:   Iimp = 12.5 kA, In = 25 kA
     *   - Classe II:  In = 20 kA, Iimp = n/a
     *   - Classe III: In = 5 kA, Iimp = n/a
     *
     * Corrente de curto (Icc): menor valor padronizado >= shortCircuitKa
     *   Tabela: [5, 10, 20, 30, 45, 65] kA
     *
     * @param  string $locationType          Tipo de localização do DPS
     * @param  int    $phaseNeutralVoltage   Tensão fase-neutro em volts
     * @param  float  $shortCircuitKa        Corrente de curto-circuito em kA
     * @return array  ['dps_class' => string, 'min_up' => float, 'uc_v' => int,
     *                 'in_value' => int, 'iimp' => int|null, 'icc' => int]
     */
    public function calculate(string $locationType, int $phaseNeutralVoltage, float $shortCircuitKa): array
    {
        // Determina classe conforme localização
        $dpsClass = match ($locationType) {
            'Descargas diretas'               => 'I',
            'Descargas Indiretas'             => 'II',
            'Proteção Individual Complementar' => 'III',
            default                           => 'II',
        };

        // Tensão de proteção máxima
        $minUp = self::UP_BY_CLASS[$dpsClass];

        // Tensão de continuidade Uc
        $ucV = match ($phaseNeutralVoltage) {
            127     => 150,
            220     => 275,
            380     => 420,
            default => (int) round($phaseNeutralVoltage * 1.1),
        };

        // Corrente nominal e de impulso conforme classe
        switch ($dpsClass) {
            case 'I':
                $inValue = 25;
                $iimp    = 12500; // 12.5 kA em amperes
                break;
            case 'II':
                $inValue = 20;
                $iimp    = null;
                break;
            case 'III':
            default:
                $inValue = 5;
                $iimp    = null;
                break;
        }

        // ICC: menor valor padronizado >= shortCircuitKa
        $icc = $this->resolveIcc($shortCircuitKa);

        return [
            'dps_class' => $dpsClass,
            'min_up'    => $minUp,
            'uc_v'      => $ucV,
            'in_value'  => $inValue,
            'iimp'      => $iimp,
            'icc'       => $icc,
        ];
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna menor valor de ICC padronizado >= shortCircuitKa (em kA).
     * Tabela: [5, 10, 20, 30, 45, 65] kA
     */
    private function resolveIcc(float $shortCircuitKa): int
    {
        $standardValues = [5, 10, 20, 30, 45, 65];

        foreach ($standardValues as $value) {
            if ($value >= $shortCircuitKa) {
                return $value;
            }
        }

        return 65;
    }
}
