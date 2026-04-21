<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

if (empty($_SESSION['sso_authenticated']) || $_SESSION['sso_authenticated'] !== true) {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

if (!empty($_SESSION['token_exp']) && $_SESSION['token_exp'] < time()) {
    session_destroy();
    header('Location: ' . baseUrl() . '/index.php?expired=1');
    exit;
}
