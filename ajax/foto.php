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

$stored = trim($row['filename']);

// MIME ikut sambungan fail (jangan percaya Content-Type dari storan)
$ext  = strtolower(pathinfo($stored, PATHINFO_EXTENSION));
$mime = match($ext) {
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    default => 'image/jpeg',
};

// Key MinIO (baru) mengandungi '/' (contoh: ea_newv3/123/foto1.jpg).
// Nama fail lokal lama sentiasa flat (contoh: wr_123_1.jpg), tiada '/'.
// MinIO di-proxy oleh pelayan: URL presigned tidak didedahkan kepada pelayar,
// dan pengguna tidak perlu akses terus ke minio.uitm.edu.my:8443.
if (strpos($stored, '/') !== false) {
    $obj = minioGetObject($stored);
    if ($obj === null) {
        http_response_code(503);
        exit;
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($obj['body']));
    header('Cache-Control: private, max-age=86400');
    header('X-Content-Type-Options: nosniff');
    echo $obj['body'];
    exit;
}

$filename = basename($stored);
$path     = __DIR__ . '/../uploads/wr/' . $filename;

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
readfile($path);
