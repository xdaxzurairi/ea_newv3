<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

// Domain production yang dibenarkan => base URL masing-masing (hos lain guna env)
$hostBases = [
    'aduanfasiliti.uitm.edu.my'    => 'https://aduanfasiliti.uitm.edu.my',
    'aduanfasilitidev.uitm.edu.my' => 'https://aduanfasilitidev.uitm.edu.my',
    'fms.uitm.edu.my'           => 'https://fms.uitm.edu.my/eAduanV2',
];
$reqHost  = strtolower(explode(':', (string)($_SERVER['HTTP_HOST'] ?? ''))[0]);
$hostBase = $hostBases[$reqHost] ?? null;

return [
    // 'google' untuk production (Google OAuth), 'portal_warga' jika Portal Warga SSO diaktifkan semula
    'auth_mode'            => getenv('AUTH_MODE')            ?: 'portal_warga',

    // Portal Warga SSO (JWT)
    'jwt_secret'           => getenv('JWT_SECRET')           ?: '',
    'jwt_algorithm'        => getenv('JWT_ALGORITHM')        ?: 'HS512',
    'portal_url'           => getenv('PORTAL_URL')           ?: 'https://portal.uitm.edu.my/login',

    // Google OAuth 2.0
    'google_client_id'     => getenv('GOOGLE_CLIENT_ID')     ?: '',
    'google_client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
    'google_redirect_uri'  => $hostBase ? $hostBase . '/auth/google_callback.php' : (getenv('GOOGLE_REDIRECT_URI') ?: ''),

    // Base URL projek (tanpa trailing slash)
    'base_url'             => $hostBase ?? (getenv('BASE_URL') ?: ''),
];
