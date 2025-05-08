<?php

return [
    'client_id' => env('MUZHIKI_CLIENT_ID'),
    'client_secret' => env('MUZHIKI_CLIENT_SECRET'),
    'redirect_uri' => env('MUZHIKI_REDIRECT_URI'),
    'auth_service_endpoint' => 'https://id.muzhiki.pro',
    'user_model' => 'App\Models\User',
    'signature' => ''
];