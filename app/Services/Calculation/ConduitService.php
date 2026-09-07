<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class ConduitService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Área externa dos cabos por bitola em mm².
     * Chave = bitola (string, ex: '1_5'); valor = área externa em mm².
     */
    private const CABLE_AREA = [
        '1_5' => 7.07,
        '2_5' => 10.18,
        '4'   => 14.07,
        '6'   => 19.63,
        '10'  => 31.67,
        '16'  => 49.02,
        '25'  => 82.52,
        '35'  => 112.0,
        '50'  => 154.0,
        '70'  => 213.0,
        '95'  => 285.0,
    ];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Tabela de eletrodutos: diâmetro_mm => área interna em mm².
     * Ocupação máxima permitida: 40%.
     */
    private const CONDUIT_TABLE = [
        16 => 113.1,
        20 => 201.1,
        25 => 314.2,
        32 => 490.9,
        40 => 804.2,
        50 => 1256.6,
    ];

    /** Ocupação máxima permitida (40%) */
    private const MAX_OCCUPATION_PERCENT = 40.0;

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Mapeamento de diâmetro em mm para polegadas.
     */
    private const INCH_MAP = [
        16 => '1/2"',
        20 => '3/4"',
        25 => '1"',
        32 => '1.1/4"',
        40 => '1.1/2"',
        50 => '2"',
    ];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula dimensionamento do eletroduto para um circuito.
     *
     * @param  array $cableCounts Quantidade de cabos por bitola ['1_5' => int, '2_5' => int, '4' => int, ...]
     * @return array ['total_cables' => int, 'dimension_mm' => int|null, 'dimension_inch' => string|null,
     *                'max_occupation' => float, 'used_conduit' => float, 'notes' => string]
     */
    public function calculate(array $cableCounts): array
    {
        $totalCables       = 0;
        $totalCableAreaMm2 = 0.0;
        $notes             = '';

        foreach ($cableCounts as $sizeKey => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }

            $area = self::CABLE_AREA[(string) $sizeKey] ?? null;

            if ($area === null) {
                $notes .= sprintf('Bitola "%s" não reconhecida. ', $sizeKey);
                continue;
            }

            $totalCables       += $qty;
            $totalCableAreaMm2 += $area * $qty;
        }

        if ($totalCables === 0) {
            return [
                'total_cables'   => 0,
                'dimension_mm'   => null,
                'dimension_inch' => null,
                'max_occupation' => self::MAX_OCCUPATION_PERCENT,
                'used_conduit'   => 0.0,
                'notes'          => trim($notes) ?: 'Nenhum cabo informado.',
            ];
        }

        // Área mínima necessária no eletroduto considerando ocupação máxima de 40%
        $requiredAreaMm2 = ($totalCableAreaMm2 * 100.0) / self::MAX_OCCUPATION_PERCENT;

        $dimensionMm   = null;
        $dimensionInch = null;
        $usedConduit   = 0.0;

        foreach (self::CONDUIT_TABLE as $diameterMm => $internalAreaMm2) {
            if ($internalAreaMm2 >= $requiredAreaMm2) {
                $dimensionMm   = $diameterMm;
                $dimensionInch = self::INCH_MAP[$diameterMm] ?? null;
                $usedConduit   = ($totalCableAreaMm2 / $internalAreaMm2) * 100.0;
                break;
            }
        }

        if ($dimensionMm === null) {
            $notes .= 'Área total dos cabos excede o maior eletroduto disponível (50 mm). ';
        }

        return [
            'total_cables'   => $totalCables,
            'dimension_mm'   => $dimensionMm,
            'dimension_inch' => $dimensionInch,
            'max_occupation' => self::MAX_OCCUPATION_PERCENT,
            'used_conduit'   => round($usedConduit, 2),
            'notes'          => trim($notes),
        ];
    }
}
