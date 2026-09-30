<?php
/**
 * eAduan v3 – Serve Foto Kerosakan (lokal legacy atau MinIO)
 * ?wr_id=X&idx=Y  (idx: 1, 2, atau 3)
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/minio.php';

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

$col = 'foto' . $idx;
// Hanya pemilik aduan boleh lihat gambar
$row = fetchOwnWr($wr_id, "{$col} AS filename");

if (!$row || empty($row['filename'])) {
    http_response_code(404);
    exit;
}

$stored = $row['filename'];

// Key MinIO (baru) mengandungi '/' (contoh: ea_newv3/wr/123/foto1.jpg).
// Nama fail lokal lama sentiasa flat (contoh: wr_123_1.jpg), tiada '/'.
if (strpos($stored, '/') !== false) {
    $url = minioPresignedUrl($stored);
    if ($url === null) {
        http_response_code(503);
        exit;
    }
    header('Location: ' . $url, true, 302);
    exit;
}

$filename = basename($stored);
$path     = __DIR__ . '/../uploads/wr/' . $filename;

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

$ext  = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
$mime = match($ext) {
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    default => 'image/jpeg',
};

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
readfile($path);
