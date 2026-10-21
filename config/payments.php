<?php

/*
|--------------------------------------------------------------------------
| Medios de pago
|--------------------------------------------------------------------------
| Cada medio usa una "pasarela" (gateway). Hoy funcionan los manuales
| (transferencia y efectivo). La tarjeta de crédito está preparada para
| la segunda actualización: solo falta completar app/Payments/CardGateway.php
| con los datos de Bancard, Pagopar u otro procesador y poner
| PAYMENTS_CARD_ENABLED=true en el archivo .env.
*/

return [

    'methods' => [
        'transferencia' => [
            'label' => 'Transferencia bancaria',
            'gateway' => 'manual',
            'web' => true,
            'local' => true,
        ],
        'efectivo' => [
            'label' => 'Efectivo al recibir',
            'gateway' => 'manual',
            'web' => true,
            'local' => true,
        ],
        'tarjeta_local' => [
            'label' => 'Tarjeta (POS del local)',
            'gateway' => 'manual',
            'web' => false,
            'local' => true,
        ],
        'tarjeta' => [
            'label' => 'Tarjeta de crédito o débito',
            'gateway' => 'card',
            'web' => true,
            'local' => false,
        ],
    ],

    'card' => [
        'enabled' => env('PAYMENTS_CARD_ENABLED', false),
        'provider' => env('PAYMENTS_CARD_PROVIDER', 'bancard'), // bancard | pagopar | otro
        'public_key' => env('PAYMENTS_CARD_PUBLIC_KEY'),
        'private_key' => env('PAYMENTS_CARD_PRIVATE_KEY'),
        'sandbox' => env('PAYMENTS_CARD_SANDBOX', true),
    ],
];
