<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceEntranceRulesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('service_entrance_rules')->truncate();

        $notes = 'Regra provisória. Ajustar conforme padrão da concessionária local.';

        DB::table('service_entrance_rules')->insert([

            // ── Monofásico (fases = 1) ──────────────────────────────────────
            [
                'phases'                   => 1,
                'min_demand_kva'                  => 0.00,
                'max_demand_kva'                  => 3.00,
                'supply_type'              => 'Monofásico 2 fios',
                'wires'                    => '2',
                'breaker_a'                => 16,
                'phase_conductor_mm2'      => 6,
                'protection_conductor_mm2' => 6,
                'pvc_conduit_mm'           => 25,
                'grounding_conductor'      => '6mm²',
                'grounding_electrodes'     => '1 haste 2,4m',
                'pontalete_type'           => 'Madeira 3"x3"',
                'notes'                    => $notes,
                'is_active'                => 1,
            ],
            [
                'phases'                   => 1,
                'min_demand_kva'                  => 3.01,
                'max_demand_kva'                  => 6.00,
                'supply_type'              => 'Monofásico 2 fios',
                'wires'                    => '2',
                'breaker_a'                => 32,
                'phase_conductor_mm2'      => 10,
                'protection_conductor_mm2' => 10,
                'pvc_conduit_mm'           => 32,
                'grounding_conductor'      => '10mm²',
                'grounding_electrodes'     => '1 haste 2,4m',
                'pontalete_type'           => null,
                'notes'                    => $notes,
                'is_active'                => 1,
            ],
            [
                'phases'                   => 1,
                'min_demand_kva'                  => 6.01,
                'max_demand_kva'                  => 8.00,
                'supply_type'              => 'Monofásico 2 fios',
                'wires'                    => '2',
                'breaker_a'                => 40,
                'phase_conductor_mm2'      => 16,
                'protection_conductor_mm2' => 16,
                'pvc_conduit_mm'           => 40,
                'grounding_conductor'      => '16mm²',
                'grounding_electrodes'     => '2 hastes 2,4m',
                'pontalete_type'           => null,
                'notes'                    => $notes,
                'is_active'                => 1,
            ],

            // ── Bifásico (fases = 2) ───────────────────────────────────────
            [
                'phases'                   => 2,
                'min_demand_kva'                  => 0.00,
                'max_demand_kva'                  => 6.00,
                'supply_type'              => 'Bifásico 3 fios',
                'wires'                    => '3',
                'breaker_a'                => 25,
                'phase_conductor_mm2'      => 6,
                'protection_conductor_mm2' => 6,
                'pvc_conduit_mm'           => 32,
                'grounding_conductor'      => '6mm²',
                'grounding_electrodes'     => '1 haste 2,4m',
                'pontalete_type'           => null,
                'notes'                    => $notes,
                'is_active'                => 1,
            ],
            [
                'phases'                   => 2,
                'min_demand_kva'                  => 6.01,
                'max_demand_kva'                  => 10.00,
                'supply_type'              => 'Bifásico 3 fios',
                'wires'                    => '3',
                'breaker_a'                => 40,
                'phase_conductor_mm2'      => 10,
                'protection_conductor_mm2' => 10,
                'pvc_conduit_mm'           => 40,
                'grounding_conductor'      => '10mm²',
                'grounding_electrodes'     => '2 hastes 2,4m',
                'pontalete_type'           => null,
                'notes'                    => $notes,
                'is_active'                => 1,
            ],

            // ── Trifásico (fases = 3) ──────────────────────────────────────
            [
                'phases'                   => 3,
                'min_demand_kva'                  => 0.00,
                'max_demand_kva'                  => 8.00,
                'supply_type'              => 'Trifásico 4 fios',
                'wires'                    => '4',
                'breaker_a'                => 25,
                'phase_conductor_mm2'      => 6,
                'protection_conductor_mm2' => 6,
                'pvc_conduit_mm'           => 32,
                'grounding_conductor'      => '6mm²',
                'grounding_electrodes'     => '1 haste 2,4m',
                'pontalete_type'           => null,
                'notes'                    => $notes,
                'is_active'                => 1,
            ],
            [
                'phases'                   => 3,
                'min_demand_kva'                  => 8.01,
                'max_demand_kva'                  => 15.00,
                'supply_type'              => 'Trifásico 4 fios',
                'wires'                    => '4',
                'breaker_a'                => 40,
                'phase_conductor_mm2'      => 10,
                'protection_conductor_mm2' => 10,
                'pvc_conduit_mm'           => 40,
                'grounding_conductor'      => '10mm²',
                'grounding_electrodes'     => '2 hastes 2,4m',
                'pontalete_type'           => null,
                'notes'                    => $notes,
                'is_active'                => 1,
            ],
        ]);
    }
}
