<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConductorAmpacitySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('conductor_ampacity')->truncate();

        DB::table('conductor_ampacity')->insert([

            // ── Método A1 — phase_group '1_2' ──────────────────────────────
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   7.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  0.75, 'iz_amperes' =>   9.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  11.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  14.5],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  19.5],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  26.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  34.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' => 10.00, 'iz_amperes' =>  46.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' => 16.00, 'iz_amperes' =>  61.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' => 25.00, 'iz_amperes' =>  80.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' => 35.00, 'iz_amperes' =>  99.0],
            ['installation_method' => 'A1', 'phase_group' => '1_2', 'conductor_mm2' => 50.00, 'iz_amperes' => 119.0],

            // ── Método A1 — phase_group '3' ────────────────────────────────
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   7.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  0.75, 'iz_amperes' =>   9.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  10.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  13.5],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  18.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  24.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  31.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' => 10.00, 'iz_amperes' =>  42.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' => 16.00, 'iz_amperes' =>  56.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' => 25.00, 'iz_amperes' =>  72.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' => 35.00, 'iz_amperes' =>  89.0],
            ['installation_method' => 'A1', 'phase_group' => '3', 'conductor_mm2' => 50.00, 'iz_amperes' => 108.0],

            // ── Método A2 — phase_group '1_2' ──────────────────────────────
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   7.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  0.75, 'iz_amperes' =>   9.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  11.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  14.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  18.5],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  25.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  32.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' => 10.00, 'iz_amperes' =>  43.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' => 16.00, 'iz_amperes' =>  57.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' => 25.00, 'iz_amperes' =>  75.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' => 35.00, 'iz_amperes' =>  92.0],
            ['installation_method' => 'A2', 'phase_group' => '1_2', 'conductor_mm2' => 50.00, 'iz_amperes' => 110.0],

            // ── Método A2 — phase_group '3' ────────────────────────────────
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   7.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  0.75, 'iz_amperes' =>   9.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  10.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  13.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  17.5],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  23.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  29.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' => 10.00, 'iz_amperes' =>  39.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' => 16.00, 'iz_amperes' =>  52.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' => 25.00, 'iz_amperes' =>  68.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' => 35.00, 'iz_amperes' =>  83.0],
            ['installation_method' => 'A2', 'phase_group' => '3', 'conductor_mm2' => 50.00, 'iz_amperes' =>  99.0],

            // ── Método B1 — phase_group '1_2' ──────────────────────────────
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   9.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  0.75, 'iz_amperes' =>  11.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  14.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  17.5],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  24.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  32.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  41.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' => 10.00, 'iz_amperes' =>  57.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' => 16.00, 'iz_amperes' =>  76.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' => 25.00, 'iz_amperes' => 101.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' => 35.00, 'iz_amperes' => 125.0],
            ['installation_method' => 'B1', 'phase_group' => '1_2', 'conductor_mm2' => 50.00, 'iz_amperes' => 151.0],

            // ── Método B1 — phase_group '3' ────────────────────────────────
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   8.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  0.75, 'iz_amperes' =>  10.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  12.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  15.5],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  21.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  28.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  36.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' => 10.00, 'iz_amperes' =>  50.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' => 16.00, 'iz_amperes' =>  68.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' => 25.00, 'iz_amperes' =>  89.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' => 35.00, 'iz_amperes' => 110.0],
            ['installation_method' => 'B1', 'phase_group' => '3', 'conductor_mm2' => 50.00, 'iz_amperes' => 134.0],

            // ── Método B2 — phase_group '1_2' ──────────────────────────────
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   9.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  0.75, 'iz_amperes' =>  11.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  13.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  16.5],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  23.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  30.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  38.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' => 10.00, 'iz_amperes' =>  52.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' => 16.00, 'iz_amperes' =>  69.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' => 25.00, 'iz_amperes' =>  90.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' => 35.00, 'iz_amperes' => 111.0],
            ['installation_method' => 'B2', 'phase_group' => '1_2', 'conductor_mm2' => 50.00, 'iz_amperes' => 133.0],

            // ── Método B2 — phase_group '3' ────────────────────────────────
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  0.50, 'iz_amperes' =>   8.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  0.75, 'iz_amperes' =>  10.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  1.00, 'iz_amperes' =>  12.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  1.50, 'iz_amperes' =>  15.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  2.50, 'iz_amperes' =>  20.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  4.00, 'iz_amperes' =>  27.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' =>  6.00, 'iz_amperes' =>  34.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' => 10.00, 'iz_amperes' =>  46.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' => 16.00, 'iz_amperes' =>  62.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' => 25.00, 'iz_amperes' =>  80.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' => 35.00, 'iz_amperes' =>  99.0],
            ['installation_method' => 'B2', 'phase_group' => '3', 'conductor_mm2' => 50.00, 'iz_amperes' => 118.0],
        ]);
    }
}
