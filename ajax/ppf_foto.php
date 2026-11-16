<?php
/**
 * eAduan v3 – Portal PPF: serve foto kerosakan rentas pemilik (staf PPF + intranet sahaja).
 * ?wr_id=X&idx=Y  (idx: 1, 2, atau 3)
 */
require_once __DIR__ . '/../includes/ppf_guard.php';
require_once __DIR__ . '/../includes/minio.php';

ppfEnforce('plain');

$wr_id = (int)req('wr_id', '0');
$idx   = (int)req('idx', '0');

if ($wr_id <= 0 || $idx < 1 || $idx > 3) {
    http_response_code(400);
    exit;
}

$col = 'foto' . $idx;
$row = dbFetchOne("SELECT {$col} AS filename FROM wr WHERE wr_id = ? AND UPPER(RTRIM(ISNULL(section, ''))) <> ?", [$wr_id, PPF_EXCLUDED_SECTION]);

if (!$row || empty($row['filename'])) {
    http_response_code(404);
    exit;
}

$stored = trim($row['filename']);

$ext  = strtolower(pathinfo($stored, PATHINFO_EXTENSION));
$mime = match($ext) {
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    default => 'image/jpeg',
};

if (strpos($stored, '/') !== false) {
    $obj = minioGetObject($stored);
    if ($obj === null) {
        http_response_code(503);
        exit;
    }
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . strlen($obj['body']));
    header('X-Content-Type-Options: nosniff');
    echo $obj['body'];
    exit;
}

$path = __DIR__ . '/../uploads/wr/' . basename($stored);

if (!file_exists($path)) {
    http_response_code(404);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
readfile($path);
