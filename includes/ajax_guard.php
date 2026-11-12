<?php
/**
 * eAduan v3 – guard untuk endpoint ajax JSON: sesi sah + token belum tamat, jika tidak 401 dengan [].
 */
require_once __DIR__ . '/session_init.php';

if (empty($_SESSION['sso_authenticated'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo '[]';
    exit;
}

if (!empty($_SESSION['token_exp']) && $_SESSION['token_exp'] < time()) {
    session_destroy();
    http_response_code(401);
    header('Content-Type: application/json');
    echo '[]';
    exit;
}
