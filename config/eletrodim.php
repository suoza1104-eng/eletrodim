<?php

return [
    'webhook_in_token'        => env('ELETRODIM_WEBHOOK_IN_TOKEN', ''),
    'webhook_out_url'         => env('ELETRODIM_WEBHOOK_OUT_URL', ''),
    'webhook_out_runner_key'  => env('ELETRODIM_WEBHOOK_OUT_RUNNER_KEY', ''),
    'share_base_url'          => env('APP_URL', 'http://localhost'),
    'pdf_storage_path'        => storage_path('app/pdfs'),
    'max_circuits'            => 50,
    'copper_resistivity'      => 0.0178, // Ω·mm²/m — resistividade do cobre
    'standard_conductors'     => [1.5, 2.5, 4, 6, 10, 16, 25, 35, 50, 70, 95],
    'standard_breakers'       => [2, 4, 6, 10, 16, 20, 25, 32, 40, 50, 63, 80, 100],
    'idr_currents'            => [25, 40, 63, 80, 100, 125],
    'icc_levels'              => [5, 10, 20, 30, 45, 65],
];
