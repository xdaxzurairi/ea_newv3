<?php
declare(strict_types=1);

/**
 * Semakan MinIO selepas deploy (dev/prod). CLI sahaja:
 *   php tools/minio_check.php
 * Upload objek kecil ke ea_newv3/_healthcheck.txt, ambil semula, banding kandungan.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/minio.php';

echo 'Config .env: ' . (is_file(__DIR__ . '/../.env.php') ? '.env.php' : (is_file(__DIR__ . '/../.env') ? '.env (TIDAK SELAMAT — tukar ke .env.php)' : 'TIADA')) . "\n";
echo 'Endpoint   : ' . (getenv('MINIO_ENDPOINT') ?: '(kosong)') . "\n";
echo 'Bucket     : ' . (getenv('MINIO_BUCKET') ?: '(kosong)') . "\n";
echo 'CA bundle  : ' . (getenv('MINIO_CA_BUNDLE') ?: (is_file(__DIR__ . '/../config/cacert.pem') ? 'config/cacert.pem' : 'php.ini curl.cainfo')) . "\n";

if (getMinioClient() === null) {
    echo "GAGAL: MINIO_* tidak lengkap dalam .env.php\n";
    exit(1);
}

$payload = 'healthcheck ' . gmdate('c');
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ea_minio_check_' . bin2hex(random_bytes(4)) . '.txt';
file_put_contents($tmp, $payload);
$key = minioUpload($tmp, '_healthcheck.txt');
@unlink($tmp);

if ($key === null) {
    echo "GAGAL: upload — semak error_log (MinioClient::putObject) untuk HTTP status / ralat SSL\n";
    exit(1);
}
$obj = minioGetObject($key);
if ($obj === null || $obj['body'] !== $payload) {
    echo "GAGAL: upload berjaya tetapi baca semula gagal\n";
    exit(1);
}
echo "OK: upload + baca semula berjaya ({$key})\n";
