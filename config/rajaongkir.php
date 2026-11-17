<?php

return [
    'api_key' => env('RAJAONGKIR_API_KEY'),

    'base_url' => env(
        'RAJAONGKIR_BASE_URL',
        'https://rajaongkir.komerce.id/api/v1'
    ),

    'package' => env('RAJAONGKIR_PACKAGE', 'starter'),
];
    