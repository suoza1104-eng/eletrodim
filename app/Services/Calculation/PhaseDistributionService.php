<?php

declare(strict_types=1);

namespace App\Services\Calculation;

class PhaseDistributionService
{
    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula carga em cada fase conforme seleção (R, S, T).
     *
     * @param  bool   $useR     Circuito conectado à fase R
     * @param  bool   $useS     Circuito conectado à fase S
     * @param  bool   $useT     Circuito conectado à fase T
     * @param  int    $phases   Quantidade de fases do circuito (1, 2 ou 3)
     * @param  float  $powerVa  Potência do circuito em VA
     * @return array  ['load_r_va' => float, 'load_s_va' => float, 'load_t_va' => float, 'error_message' => ?string]
     */
    public function calculatePhaseLoad(bool $useR, bool $useS, bool $useT, int $phases, float $powerVa): array
    {
        $loadR        = 0.0;
        $loadS        = 0.0;
        $loadT        = 0.0;
        $errorMessage = null;

        $selectedPhases = array_filter(['R' => $useR, 'S' => $useS, 'T' => $useT]);
        $countSelected  = count($selectedPhases);

        // Validação: quantidade de fases selecionadas deve ser compatível com $phases
        if ($countSelected === 0) {
            $errorMessage = 'Nenhuma fase selecionada para o circuito.';
            return [
                'load_r_va'     => $loadR,
                'load_s_va'     => $loadS,
                'load_t_va'     => $loadT,
                'error_message' => $errorMessage,
            ];
        }

        if ($countSelected !== $phases && $phases !== 3) {
            $errorMessage = sprintf(
                'Circuito %d fase(s) com %d fase(s) selecionada(s).',
                $phases,
                $countSelected
            );
        }

        // Trifásico: carga distribuída igualmente entre R, S e T
        if ($phases === 3) {
            $loadPerPhase = $powerVa / 3.0;
            $loadR        = $loadPerPhase;
            $loadS        = $loadPerPhase;
            $loadT        = $loadPerPhase;

            return [
                'load_r_va'     => $loadR,
                'load_s_va'     => $loadS,
                'load_t_va'     => $loadT,
                'error_message' => $errorMessage,
            ];
        }

        // Monofásico / bifásico: distribui igualmente pelas fases marcadas
        if ($countSelected > 0) {
            $loadPerPhase = $powerVa / $countSelected;

            if ($useR) {
                $loadR = $loadPerPhase;
            }
            if ($useS) {
                $loadS = $loadPerPhase;
            }
            if ($useT) {
                $loadT = $loadPerPhase;
            }
        }

        return [
            'load_r_va'     => $loadR,
            'load_s_va'     => $loadS,
            'load_t_va'     => $loadT,
            'error_message' => $errorMessage,
        ];
    }

    // Regra provisória baseada na estrutura da planilha. Pode ser ajustada quando o script original for fornecido.
    /**
     * Calcula totais de cada fase somando todos os circuitos e avalia desequilíbrio.
     *
     * Status:
     *   'good'     se desequilíbrio <= 10%
     *   'warning'  se desequilíbrio entre 10% e 20%
     *   'critical' se desequilíbrio > 20%
     *
     * @param  array  $phaseDistributions Array de resultados de calculatePhaseLoad()
     * @return array  ['total_r' => float, 'total_s' => float, 'total_t' => float, 'imbalance_percent' => float, 'status' => string]
     */
    public function calculateTotals(array $phaseDistributions): array
    {
        $totalR = 0.0;
        $totalS = 0.0;
        $totalT = 0.0;

        foreach ($phaseDistributions as $dist) {
            $totalR += (float) ($dist['load_r_va'] ?? 0.0);
            $totalS += (float) ($dist['load_s_va'] ?? 0.0);
            $totalT += (float) ($dist['load_t_va'] ?? 0.0);
        }

        $totalSum = $totalR + $totalS + $totalT;
        $average  = $totalSum > 0.0 ? $totalSum / 3.0 : 0.0;

        $imbalancePercent = 0.0;
        if ($average > 0.0) {
            $maxDeviation     = max(
                abs($totalR - $average),
                abs($totalS - $average),
                abs($totalT - $average)
            );
            $imbalancePercent = ($maxDeviation / $average) * 100.0;
        }

        if ($imbalancePercent <= 10.0) {
            $status = 'good';
        } elseif ($imbalancePercent <= 20.0) {
            $status = 'warning';
        } else {
            $status = 'critical';
        }

        return [
            'total_r'           => $totalR,
            'total_s'           => $totalS,
            'total_t'           => $totalT,
            'imbalance_percent' => $imbalancePercent,
            'status'            => $status,
        ];
    }
}
