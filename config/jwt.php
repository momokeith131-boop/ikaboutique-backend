<?php

return [
    'secret' => env('JWT_SECRET', 'your-secret-key-here'),
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'ttl' => env('JWT_TTL', 60),
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
    'algo' => env('JWT_ALGORITHM', 'HS256'),
    'encode' => [
        'alg' => env('JWT_ALGORITHM', 'HS256'),
        'type' => 'JWT',
    ],
    'decode' => [
        'verify_signature' => env('JWT_VERIFY_SIGNATURE', true),
        'allowed_algs' => [env('JWT_ALGORITHM', 'HS256')],
    ],
];
