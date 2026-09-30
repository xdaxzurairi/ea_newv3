<?php
declare(strict_types=1);

require_once __DIR__ . '/S3.php';
require_once __DIR__ . '/../config/env.php';

/** Namespace prefix supaya key kita tidak berlanggar dengan modul FMS lain yang kongsi bucket sama. */
const MINIO_KEY_PREFIX = 'ea_newv3/';

function getMinioClient(): ?MinioClient
{
    static $client = null;
    static $tried  = false;

    if ($tried) {
        return $client;
    }
    $tried = true;

    $endpoint = getenv('MINIO_ENDPOINT') ?: '';
    $access   = getenv('MINIO_ACCESS_KEY') ?: '';
    $secret   = getenv('MINIO_SECRET_KEY') ?: '';
    $bucket   = getenv('MINIO_BUCKET') ?: '';

    if ($endpoint === '' || $access === '' || $secret === '' || $bucket === '') {
        return null;
    }

    $region    = getenv('MINIO_REGION') ?: 'us-east-1';
    $pathStyle = (getenv('MINIO_USE_PATH_STYLE') ?: 'true') !== 'false';

    $client = new MinioClient($endpoint, $access, $secret, $region, $bucket, $pathStyle);
    return $client;
}

/**
 * Muat naik fail lokal ke MinIO. Pulang object key (dengan prefix) jika berjaya, null jika gagal.
 * $key diterima TANPA prefix — fungsi ini yang tambah MINIO_KEY_PREFIX.
 */
function minioUpload(string $localPath, string $key): ?string
{
    $client = getMinioClient();
    if ($client === null) {
        return null;
    }

    $body = @file_get_contents($localPath);
    if ($body === false) {
        error_log("minioUpload: gagal baca fail lokal {$localPath}");
        return null;
    }

    $ext  = strtolower(pathinfo($localPath, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        default => 'image/jpeg',
    };

    $fullKey = MINIO_KEY_PREFIX . ltrim($key, '/');

    if (!$client->putObject($fullKey, $body, $mime)) {
        return null;
    }

    return $fullKey;
}

/** Ambil kandungan objek (server-side). Pulang ['body','type'] atau null. */
function minioGetObject(string $key): ?array
{
    $client = getMinioClient();
    return $client ? $client->getObject($key) : null;
}

/** Jana presigned GET URL untuk object key (key sudah termasuk prefix, seperti disimpan dalam DB). */
function minioPresignedUrl(string $key, int $expirySeconds = 300): ?string
{
    $client = getMinioClient();
    if ($client === null) {
        return null;
    }
    return $client->presignedGetUrl($key, $expirySeconds);
}
