<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class CircuitCalculationService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Consolida dados de um circuito a partir de load_rows e input_rows.
     *
     * @param  int    $circuitNumber Número do circuito
     * @param  array  $loadRows      Linhas de project_load_rows deste circuito
     * @param  array  $inputRows     Linhas de project_input_rows correspondentes
     * @return array  Dados consolidados do circuito
     */
    public function consolidateCircuit(int $circuitNumber, array $loadRows, array $inputRows): array
    {
        $errorMessages = [];
        $hasErrors     = false;

        // Descrições dos cômodos
        $descriptions = [];
        foreach ($loadRows as $row) {
            $desc = (string) ($row['room_description'] ?? '');
            if ($desc !== '') {
                $descriptions[] = $desc;
            }
        }
        $description = implode(', ', array_unique($descriptions));

        // Somas simples
        $lightingVa    = 0.0;
        $outlet100Qty  = 0;
        $outlet600Qty  = 0;
        $outlet1000Qty = 0;
        $tueVa         = 0.0;
        $powerW        = 0.0;
        $powerVa       = 0.0;

        foreach ($loadRows as $row) {
            $lightingVa    += (float) ($row['lighting_va']     ?? 0.0);
            $outlet100Qty  += (int)   ($row['outlet_100_qty']  ?? 0);
            $outlet600Qty  += (int)   ($row['outlet_600_qty']  ?? 0);
            $outlet1000Qty += (int)   ($row['outlet_1000_qty'] ?? 0);
            $tueVa         += (float) ($row['tue_va']          ?? 0.0);
            $powerW        += (float) ($row['power_w']         ?? 0.0);
            $powerVa       += (float) ($row['power_va']        ?? 0.0);
        }

        // Fator de potência consolidado
        $powerFactor = $powerVa > 0.0 ? $powerW / $powerVa : 1.0;

        // Tipo de circuito
        $circuitType = $this->determineCircuitType($lightingVa, $outlet100Qty + $outlet600Qty + $outlet1000Qty, $tueVa);

        // Valores que devem ser iguais em todas as linhas do circuito
        $phases             = $this->resolveUniformField($inputRows, 'phases', 'ERRO');
        $voltage            = $this->resolveUniformField($inputRows, 'voltage', 'ERRO');
        $installationMethod = $this->resolveUniformField($inputRows, 'installation_method', 'ERRO');

        if ($phases === 'ERRO') {
            $hasErrors       = true;
            $errorMessages[] = 'Fases divergentes no circuito ' . $circuitNumber . '.';
        }
        if ($voltage === 'ERRO') {
            $hasErrors       = true;
            $errorMessages[] = 'Tensão divergente no circuito ' . $circuitNumber . '.';
        }
        if ($installationMethod === 'ERRO') {
            $hasErrors       = true;
            $errorMessages[] = 'Método de instalação divergente no circuito ' . $circuitNumber . '.';
        }

        // Valores máximos / mínimos das linhas
        $temperatureC      = $this->resolveMaxField($inputRows, 'temperature_c', 30);
        $distanceM         = $this->resolveMaxField($inputRows, 'distance_m', 0);
        $voltageDropPercent = $this->resolveMinField($inputRows, 'voltage_drop_percent', 7.0);
        $groupedCircuits   = $this->resolveMaxField($inputRows, 'grouped_circuits', 1);

        // Corrente de projeto
        $phasesInt       = is_numeric($phases) ? (int) $phases : 0;
        $voltageFloat    = is_numeric($voltage) ? (float) $voltage : 0.0;
        $projectCurrentA = $this->calculateProjectCurrent($powerVa, $phasesInt, $voltageFloat);

        return [
            'circuit_number'       => $circuitNumber,
            'description'          => $description,
            'circuit_type'         => $circuitType,
            'lighting_va'          => $lightingVa,
            'outlet_100_qty'       => $outlet100Qty,
            'outlet_600_qty'       => $outlet600Qty,
            'outlet_1000_qty'      => $outlet1000Qty,
            'tue_va'               => $tueVa,
            'power_w'              => $powerW,
            'power_va'             => $powerVa,
            'power_factor'         => $powerFactor,
            'phases'               => $phases,
            'voltage'              => $voltage,
            'installation_method'  => $installationMethod,
            'temperature_c'        => $temperatureC,
            'distance_m'           => $distanceM,
            'voltage_drop_percent' => $voltageDropPercent,
            'grouped_circuits'     => $groupedCircuits,
            'project_current_a'    => $projectCurrentA,
            'has_errors'           => $hasErrors,
            'error_messages'       => $errorMessages,
        ];
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Determina o tipo de circuito com base nas cargas presentes.
     */
    private function determineCircuitType(float $lightingVa, int $totalOutlets, float $tueVa): string
    {
        $hasLighting = $lightingVa > 0.0;
        $hasTug      = $totalOutlets > 0;
        $hasTue      = $tueVa > 0.0;

        if ($hasTue && !$hasLighting && !$hasTug) {
            return 'TUE';
        }
        if ($hasLighting && !$hasTug && !$hasTue) {
            return 'ILUMINAÇÃO';
        }
        if ($hasTug && !$hasLighting && !$hasTue) {
            return 'TUG';
        }
        if ($hasLighting && ($hasTug || $hasTue)) {
            return 'MISTO';
        }
        if ($hasTug && $hasTue) {
            return 'MISTO';
        }

        return 'GERAL';
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna o valor do campo se for igual em todas as linhas; caso contrário, retorna $fallback.
     */
    private function resolveUniformField(array $rows, string $field, mixed $fallback): mixed
    {
        $values = array_unique(array_column($rows, $field));

        if (count($values) === 1) {
            return reset($values);
        }

        return $fallback;
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna o maior valor numérico de um campo nas linhas.
     */
    private function resolveMaxField(array $rows, string $field, mixed $default): mixed
    {
        $values = array_filter(
            array_column($rows, $field),
            fn($v) => $v !== null && is_numeric($v)
        );

        if (empty($values)) {
            return $default;
        }

        return max($values);
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Retorna o menor valor numérico de um campo nas linhas.
     */
    private function resolveMinField(array $rows, string $field, mixed $default): mixed
    {
        $values = array_filter(
            array_column($rows, $field),
            fn($v) => $v !== null && is_numeric($v)
        );

        if (empty($values)) {
            return $default;
        }

        return min($values);
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula corrente de projeto conforme fases e tensão.
     */
    private function calculateProjectCurrent(float $powerVa, int $phases, float $voltage): float
    {
        if ($voltage <= 0.0) {
            return 0.0;
        }

        if ($phases === 3) {
            return $powerVa / (1.73 * $voltage);
        }

        return $powerVa / $voltage;
    }
}
