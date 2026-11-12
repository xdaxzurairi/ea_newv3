<?php
/**
 * eAduan v3 – mulakan sesi dengan atribut cookie selamat (HttpOnly, SameSite=Lax, Secure di HTTPS).
 * Lax (bukan Strict) supaya cookie kekal dihantar selepas redirect balik dari Google.
 * Ganti session_start() dengan require_once fail ini.
 */
if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
