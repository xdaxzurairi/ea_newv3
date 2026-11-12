<?php
/**
 * eAduan v3 – Serve foto sementara (uploads/temp) untuk pratonton
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    http_response_code(403);
    exit;
}

$fn = trim($_GET['f'] ?? '');
if (!preg_match('/^tmp_[a-zA-Z0-9]+_\d+_\d+\.jpg$/', $fn)) {
    http_response_code(404);
    exit;
}

$token = uploadToken();
if (strpos($fn, 'tmp_' . $token . '_') !== 0) {
    http_response_code(403);
    exit;
}

$path = __DIR__ . '/../uploads/temp/' . $fn;
if (!is_file($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: image/jpeg');
header('Cache-Control: private, max-age=300');
readfile($path);
