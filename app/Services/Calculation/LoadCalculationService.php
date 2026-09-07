<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class LoadCalculationService
{
    /**
     * Calcula potência W e VA de uma linha de project_input_rows, e a corrente de projeto.
     *
     * A potência (specific_power_va) já vem corretamente calculada/atribuída pelo
     * wizard (ProjectWizard::calcMinLightingVa / calcMinTugData, com suporte a
     * ajuste manual, TUEs e cargas divididas) - este serviço apenas aplica o
     * fator de potência e calcula a corrente, sem re-derivar a potência a partir
     * da geometria do cômodo.
     *
     * Retorna array com: lighting_va, outlet_100_qty, outlet_600_qty, outlet_1000_qty,
     *                    tue_va, power_factor, power_w, power_va, project_current_a, error_message
     */
    public function calculateInputRow(array $inputRow): array
    {
        $errorMessage = null;

        $powerVa     = (float) ($inputRow['specific_power_va'] ?? 0.0);
        $loadType    = (string) ($inputRow['load_type']        ?? '');
        $quantity    = (int)   ($inputRow['quantity']          ?? 1);
        $powerFactor = (float) ($inputRow['power_factor']      ?? 1.0);
        $voltage     = (float) ($inputRow['voltage']           ?? 0.0);
        $phases      = (int)   ($inputRow['phases']            ?? 1);

        $lightingVa = $loadType === 'ILUMINAÇÃO' ? $powerVa : 0.0;
        $tueVa      = $loadType === 'TUE' ? $powerVa : 0.0;

        // Classificação informativa das tomadas TUG pela potência unitária (NBR 5410).
        $outlet100Qty = $outlet600Qty = $outlet1000Qty = 0;
        if ($loadType === 'TUG' && $quantity > 0) {
            $unitVa = $powerVa / $quantity;
            match (true) {
                $unitVa >= 1000 => $outlet1000Qty = $quantity,
                $unitVa >= 600  => $outlet600Qty = $quantity,
                default         => $outlet100Qty = $quantity,
            };
        }

        // Potência W = VA * fator de potência
        if ($powerFactor <= 0.0 || $powerFactor > 1.0) {
            $powerFactor  = 1.0;
            $errorMessage = 'Fator de potência inválido; assumido 1.0.';
        }
        $powerW = $powerVa * $powerFactor;

        // Corrente de projeto
        $projectCurrentA = 0.0;
        if ($voltage > 0.0) {
            $projectCurrentA = $phases === 3
                ? $powerVa / (1.73 * $voltage)
                : $powerVa / $voltage;
        }

        return [
            'lighting_va'       => $lightingVa,
            'outlet_100_qty'    => $outlet100Qty,
            'outlet_600_qty'    => $outlet600Qty,
            'outlet_1000_qty'   => $outlet1000Qty,
            'tue_va'            => $tueVa,
            'power_factor'      => $powerFactor,
            'power_w'           => $powerW,
            'power_va'          => $powerVa,
            'project_current_a' => $projectCurrentA,
            'error_message'     => $errorMessage,
        ];
    }
}
