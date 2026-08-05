<?php
declare(strict_types=1);

/**
 * Klien S3-compatible ringkas (AWS Signature V4) untuk MinIO.
 * Single-file, tiada dependency luar — guna cURL + hash bawaan PHP sahaja.
 * Sokongan: putObject (upload) & presignedGetUrl (serve dengan expiry terhad).
 */
class MinioClient
{
    private string $endpoint;
    private string $host;
    private string $accessKey;
    private string $secretKey;
    private string $region;
    private string $bucket;
    private bool   $pathStyle;

    public function __construct(
        string $endpoint,
        string $accessKey,
        string $secretKey,
        string $region,
        string $bucket,
        bool $pathStyle = true
    ) {
        $this->endpoint  = rtrim($endpoint, '/');
        $host            = parse_url($this->endpoint, PHP_URL_HOST) ?: '';
        $port            = parse_url($this->endpoint, PHP_URL_PORT);
        $this->host      = $port ? "{$host}:{$port}" : $host;
        $this->accessKey = $accessKey;
        $this->secretKey = $secretKey;
        $this->region    = $region;
        $this->bucket    = $bucket;
        $this->pathStyle = $pathStyle;
    }

    /** Muat naik $body ke $key dalam bucket. Pulang true jika HTTP 2xx. */
    public function putObject(string $key, string $body, string $contentType = 'application/octet-stream'): bool
    {
        $uri  = $this->objectUri($key);
        $now  = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);

        $payloadHash = 'UNSIGNED-PAYLOAD';
        $headers = [
            'content-type'         => $contentType,
            'host'                 => $this->host,
            'x-amz-content-sha256' => $payloadHash,
            'x-amz-date'           => $now,
        ];
        ksort($headers);

        $signedHeaders    = implode(';', array_keys($headers));
        $canonicalHeaders = '';
        foreach ($headers as $k => $v) {
            $canonicalHeaders .= $k . ':' . trim($v) . "\n";
        }

        $credentialScope  = "{$date}/{$this->region}/s3/aws4_request";
        $canonicalRequest = "PUT\n{$uri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$payloadHash}";
        $stringToSign     = "AWS4-HMAC-SHA256\n{$now}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);
        $signature        = $this->sign($stringToSign, $date);

        $authHeader = "AWS4-HMAC-SHA256 Credential={$this->accessKey}/{$credentialScope}, "
                    . "SignedHeaders={$signedHeaders}, Signature={$signature}";

        $ch = curl_init($this->endpoint . $uri);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Host: ' . $this->host,
                'Content-Type: ' . $contentType,
                'x-amz-content-sha256: ' . $payloadHash,
                'x-amz-date: ' . $now,
                'Authorization: ' . $authHeader,
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $this->applyCaBundle($ch);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err    = curl_error($ch);
        curl_close($ch);

        if ($status < 200 || $status >= 300) {
            error_log("MinioClient::putObject gagal (HTTP {$status}) untuk key={$key}: {$err}");
            return false;
        }
        return true;
    }

    /**
     * php.ini curl.cainfo pada sesetengah pelayan Windows dev menunjuk ke fail
     * yang tak wujud (cth. C:\apache24\bin\curl-ca-bundle.crt), menyebabkan
     * setiap permintaan HTTPS gagal (HTTP 0). Jika ini berlaku, cari bundle CA
     * sah yang lain (biasa dibundle dengan Git for Windows) sebagai gantian.
     */
    private function applyCaBundle($ch): void
    {
        $configured = ini_get('curl.cainfo');
        if ($configured && is_file($configured)) {
            return; // ini sudah betul, tiada perlu override
        }

        $candidates = [
            'C:/Program Files/Git/mingw64/etc/ssl/certs/ca-bundle.crt',
            'C:/Program Files/Git/usr/ssl/certs/ca-bundle.crt',
            'C:/xampp/apache/bin/curl-ca-bundle.crt',
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                curl_setopt($ch, CURLOPT_CAINFO, $path);
                return;
            }
        }
    }

    /** Jana presigned URL (query-string SigV4) untuk GET objek $key, sah selama $expiresSeconds. */
    public function presignedGetUrl(string $key, int $expiresSeconds = 86400): string
    {
        $uri  = $this->objectUri($key);
        $now  = gmdate('Ymd\THis\Z');
        $date = substr($now, 0, 8);

        $credentialScope = "{$date}/{$this->region}/s3/aws4_request";
        $credential      = "{$this->accessKey}/{$credentialScope}";

        $query = [
            'X-Amz-Algorithm'     => 'AWS4-HMAC-SHA256',
            'X-Amz-Credential'    => $credential,
            'X-Amz-Date'          => $now,
            'X-Amz-Expires'       => (string) $expiresSeconds,
            'X-Amz-SignedHeaders' => 'host',
        ];
        ksort($query);
        $canonicalQuery = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        $canonicalHeaders = "host:{$this->host}\n";
        $canonicalRequest = "GET\n{$uri}\n{$canonicalQuery}\n{$canonicalHeaders}\nhost\nUNSIGNED-PAYLOAD";
        $stringToSign      = "AWS4-HMAC-SHA256\n{$now}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);
        $signature         = $this->sign($stringToSign, $date);

        return $this->endpoint . $uri . '?' . $canonicalQuery . '&X-Amz-Signature=' . $signature;
    }

    private function objectUri(string $key): string
    {
        $key = ltrim($key, '/');
        $encoded = implode('/', array_map('rawurlencode', explode('/', $key)));
        return $this->pathStyle
            ? '/' . rawurlencode($this->bucket) . '/' . $encoded
            : '/' . $encoded;
    }

    private function sign(string $stringToSign, string $date): string
    {
        $kDate    = hash_hmac('sha256', $date, 'AWS4' . $this->secretKey, true);
        $kRegion  = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', 's3', $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        return hash_hmac('sha256', $stringToSign, $kSigning, false);
    }
}
