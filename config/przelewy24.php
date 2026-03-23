<?php

return [

    'merchant_id' => (int) env('P24_MERCHANT_ID', 0),

    'pos_id' => env('P24_POS_ID') !== null ? (int) env('P24_POS_ID') : null,

    'reports_key' => env('P24_REPORTS_KEY', ''),

    'crc' => env('P24_CRC', ''),

    'sandbox' => filter_var(env('P24_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),

    'verify_webhook_ip' => filter_var(env('P24_VERIFY_WEBHOOK_IP', false), FILTER_VALIDATE_BOOLEAN),

];
