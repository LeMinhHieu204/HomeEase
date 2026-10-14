<?php

return [
    'driver' => env('PAYMENT_DRIVER', 'stripe'),
    'stripe_secret' => env('STRIPE_SECRET'),
    'stripe_webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
];
