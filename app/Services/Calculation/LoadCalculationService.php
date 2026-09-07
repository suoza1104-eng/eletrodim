<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class LoadCalculationService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula potência de iluminação com base na área.
     * Regra: área <= 6m² => 100 VA; área > 6m² => 100 + ceil((area-6)/4)*60
     */
    public function calculateLighting(float $areaMq): float
    {
        if ($areaMq <= 6.0) {
            return 100.0;
        }

        return 100.0 + (float) (ceil(($areaMq - 6.0) / 4.0) * 60);
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula tomadas conforme tipo de cômodo e perímetro.
     * Retorna array ['qty_100' => int, 'qty_600' => int, 'qty_1000' => int]
     */
    public function calculateOutlets(float $perimeter, string $roomType): array
    {
        $type = mb_strtoupper(trim($roomType));

        // BANHEIRO
        if ($type === 'BANHEIRO') {
            return ['qty_100' => 0, 'qty_600' => 0, 'qty_1000' => 1];
        }

        // Cômodos tipo cozinha/copa/lavanderia
        $kitchenTypes = [
            'COZINHA',
            'COPA-COZINHA',
            'COPA',
            'LAVANDERIA',
            'COZINHA-ÁREA DE SERVIÇO',
        ];

        if (in_array($type, $kitchenTypes, true)) {
            $qty = (int) ceil($perimeter / 3.5);
            if ($qty <= 3) {
                return ['qty_100' => 0, 'qty_600' => $qty, 'qty_1000' => 0];
            }
            return ['qty_100' => $qty - 3, 'qty_600' => 3, 'qty_1000' => 0];
        }

        // Sala de máquinas / oficina: igual cozinha + qty_1000 = 1 adicional
        if ($type === 'SALA DE MÁQUINAS - OFICINA OU SEMELHANTES') {
            $qty = (int) ceil($perimeter / 3.5);
            if ($qty <= 3) {
                return ['qty_100' => 0, 'qty_600' => $qty, 'qty_1000' => 1];
            }
            return ['qty_100' => $qty - 3, 'qty_600' => 3, 'qty_1000' => 1];
        }

        // Demais cômodos
        $qty = (int) ceil($perimeter / 5.0);
        return ['qty_100' => $qty, 'qty_600' => 0, 'qty_1000' => 0];
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula potência total de TUG (tomadas).
     * (qty_100*100) + (qty_600*600) + (qty_1000*1000)
     */
    public function calculateTugPower(array $outlets): float
    {
        $qty100  = (int) ($outlets['qty_100']  ?? 0);
        $qty600  = (int) ($outlets['qty_600']  ?? 0);
        $qty1000 = (int) ($outlets['qty_1000'] ?? 0);

        return (float) (($qty100 * 100) + ($qty600 * 600) + ($qty1000 * 1000));
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula potência W e VA de uma linha, e corrente de projeto.
     * Retorna array com: lighting_va, outlet_100_qty, outlet_600_qty, outlet_1000_qty,
     *                    tue_va, power_factor, power_w, power_va, project_current_a, error_message
     */
    public function calculateInputRow(array $inputRow): array
    {
        $errorMessage = null;

        $areaMq      = (float) ($inputRow['area_mq']      ?? 0.0);
        $perimeter   = (float) ($inputRow['perimeter_m']  ?? 0.0);
        $roomType    = (string) ($inputRow['room_type']   ?? '');
        $tueVa       = (float) ($inputRow['tue_va']       ?? 0.0);
        $powerFactor = (float) ($inputRow['power_factor'] ?? 1.0);
        $voltage     = (float) ($inputRow['voltage']      ?? 0.0);
        $phases      = (int)   ($inputRow['phases']       ?? 1);

        // Iluminação
        $lightingVa = $this->calculateLighting($areaMq);

        // Tomadas
        $outlets = $this->calculateOutlets($perimeter, $roomType);

        // Potência TUG
        $tugPower = $this->calculateTugPower($outlets);

        // Potência VA total = iluminação + TUG + TUE
        $powerVa = $lightingVa + $tugPower + $tueVa;

        // Potência W = VA * fator de potência
        if ($powerFactor <= 0.0 || $powerFactor > 1.0) {
            $powerFactor  = 1.0;
            $errorMessage = 'Fator de potência inválido; assumido 1.0.';
        }
        $powerW = $powerVa * $powerFactor;

        // Corrente de projeto
        $projectCurrentA = 0.0;
        if ($voltage > 0.0) {
            if ($phases === 3) {
                $projectCurrentA = $powerVa / (1.73 * $voltage);
            } else {
                $projectCurrentA = $powerVa / $voltage;
            }
        }

        return [
            'lighting_va'       => $lightingVa,
            'outlet_100_qty'    => $outlets['qty_100'],
            'outlet_600_qty'    => $outlets['qty_600'],
            'outlet_1000_qty'   => $outlets['qty_1000'],
            'tue_va'            => $tueVa,
            'power_factor'      => $powerFactor,
            'power_w'           => $powerW,
            'power_va'          => $powerVa,
            'project_current_a' => $projectCurrentA,
            'error_message'     => $errorMessage,
        ];
    }
}
