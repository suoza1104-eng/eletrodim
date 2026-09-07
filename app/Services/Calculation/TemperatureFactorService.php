<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class TemperatureFactorService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Tabela de fatores de temperatura.
     * Chave = temperatura em °C; valor = fator de correção.
     */
    private const TEMPERATURE_FACTOR_TABLE = [
        10 => 1.22,
        15 => 1.17,
        20 => 1.12,
        25 => 1.06,
        30 => 1.00,
        35 => 0.94,
        40 => 0.87,
        45 => 0.79,
        50 => 0.71,
        55 => 0.61,
        60 => 0.50,
    ];

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna fator de temperatura para a temperatura informada.
     *
     * @param  int        $temperatureC Temperatura em graus Celsius
     * @return float|null Fator de correção, ou null se temperatura não constar na tabela
     */
    public function getFactor(int $temperatureC): ?float
    {
        return self::TEMPERATURE_FACTOR_TABLE[$temperatureC] ?? null;
    }
}
