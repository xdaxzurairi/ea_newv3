<?php
/**
 * eAduan v3 – Serve Foto Kerosakan dari DB
 * ?wr_id=X&idx=Y  (idx: 1, 2, atau 3)
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    http_response_code(403);
    exit;
}

$wr_id = (int)req('wr_id', '0');
$idx   = (int)req('idx', '0');

if ($wr_id <= 0 || $idx < 1 || $idx > 3) {
    http_response_code(400);
    exit;
}

$row = dbFetchOne(
    "SELECT foto_data, mime_type FROM wr_photos WHERE wr_id = ? AND foto_index = ?",
    [$wr_id, $idx]
);

if (!$row || empty($row['foto_data'])) {
    http_response_code(404);
    exit;
}

$mime = $row['mime_type'] ?: 'image/jpeg';
$data = is_resource($row['foto_data']) ? stream_get_contents($row['foto_data']) : $row['foto_data'];

header('Content-Type: ' . $mime);
header('Content-Length: ' . strlen($data));
header('Cache-Control: private, max-age=86400');
echo $data;
