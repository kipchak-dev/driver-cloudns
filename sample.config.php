<?php

use function Kipchak\Core\env;

return [
    'connections' => [
        'default' => [
            // Standard API user — create one at ClouDNS > API & Resellers
            'auth_id'       => env('CLOUDNS_AUTH_ID', ''),
            'auth_password' => env('CLOUDNS_AUTH_PASSWORD', ''),

            // Sub-user alternatives — use one of these instead of auth_id:
            // 'sub_auth_id'   => env('CLOUDNS_SUB_AUTH_ID', ''),
            // 'sub_auth_user' => env('CLOUDNS_SUB_AUTH_USER', ''),

            // Optional: override the API base URL
            // 'base_url' => 'https://api.cloudns.net',
        ],
    ],
];
