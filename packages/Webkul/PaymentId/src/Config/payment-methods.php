<?php

return [
    'midtrans' => [
        'code'             => 'midtrans',
        'title'            => 'Midtrans',
        'description'      => 'Bayar dengan Midtrans — transfer bank, kartu kredit, e-wallet, gerai retail',
        'class'            => \Webkul\PaymentId\Payment\Midtrans::class,
        'active'           => false,
        'sandbox'          => true,
        'client_key'       => '',
        'server_key'       => '',
        'merchant_id'      => '',
        'enabled_channels' => 'bca_va,bni_va,bri_va,gopay,shopeepay,qris',
        'sort'             => 10,
    ],
];
