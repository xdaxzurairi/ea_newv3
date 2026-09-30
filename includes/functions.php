<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Kuala_Lumpur');

/**
 * Hantar HTTP security headers untuk semua response web.
 * Dipanggil secara automatik apabila fail ini di-load.
 */
function sendSecurityHeaders(): void
{
    // Elak untuk CLI / jika headers sudah dihantar
    if (PHP_SAPI === 'cli' || headers_sent()) {
        return;
    }

    // Strict Transport Security (diabaikan jika bukan HTTPS)
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

    // Content Security Policy – larangan/benaran sumber
    header('Content-Security-Policy: ' . buildContentSecurityPolicy());

    // Clickjacking, MIME sniffing, referrer, permissions
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(self), microphone=(), camera=()');
}

/**
 * CSP khusus untuk eAduan v3 berdasarkan domain yang digunakan.
 */
function buildContentSecurityPolicy(): string
{
    $directives = [
        "default-src 'self'",
        // Inline script digunakan (contoh di borang.php) + CDN & Leaflet
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com",
        // Bootstrap CSS, icons & Google Fonts
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
        // Font dari Google Fonts & CDN
        "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
        // Imej + tile peta Google (mt0-3.google.com)
        "img-src 'self' data: blob: https://*.google.com",
        // AJAX / fetch hanya ke domain sendiri
        "connect-src 'self'",
        // Halaman ini hanya boleh di-frame oleh origin yang sama
        "frame-ancestors 'self'",
        // Borang hanya submit ke domain sendiri
        "form-action 'self'",
        // Elak serangan base tag injection
        "base-uri 'self'",
        // Tiada plugin legacy seperti Flash/Java
        "object-src 'none'",
    ];

    return implode('; ', $directives);
}

// Panggil secara automatik untuk semua request web
sendSecurityHeaders();

function getPdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = require __DIR__ . '/../config/database.php';
    }
    return $pdo;
}

function getAuthConfig(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/auth.php';
    }
    return $cfg;
}

function baseUrl(): string
{
    $cfg = getAuthConfig();
    return rtrim($cfg['base_url'] ?? '', '/');
}

/** Run a SELECT and return all rows */
function dbFetchAll(string $sql, array $params = []): array
{
    $stmt = getPdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/** Run a SELECT and return first row or null */
function dbFetchOne(string $sql, array $params = []): ?array
{
    $stmt = getPdo()->prepare($sql);
    $stmt->execute($params);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

/** Run a SELECT and return single column value */
function dbFetchColumn(string $sql, array $params = [])
{
    $stmt = getPdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

/** Run INSERT/UPDATE/DELETE and return affected rows */
function dbExecute(string $sql, array $params = []): int
{
    $stmt = getPdo()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/** Escape for HTML output */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** Get request param with trim */
function req(string $key, string $default = ''): string
{
    return trim((string) ($_REQUEST[$key] ?? $default));
}

/** Get POST param with trim */
function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

/** Session value */
function sess(string $key, $default = null)
{
    return $_SESSION[$key] ?? $default;
}

/** Jana atau dapatkan CSRF token untuk sesi semasa */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Sahkan CSRF token dari POST; hentikan request jika gagal */
function validateCsrf(): void
{
    $token = (string) ($_POST['csrf_token'] ?? '');
    if (
        empty($_SESSION['csrf_token']) ||
        !hash_equals((string) $_SESSION['csrf_token'], $token)
    ) {
        http_response_code(403);
        die('Permintaan tidak sah. Sila refresh halaman dan cuba semula.');
    }
}

/** Jana session ID baru selepas login (elak session fixation) */
function regenerateLoginSession(): void
{
    session_regenerate_id(true);
    unset($_SESSION['csrf_token']);
}

/**
 * Identiti pengguna semasa dari sesi: [kolum wr, nilai].
 * Pulang null jika sesi tiada identiti sah.
 */
function currentOwner(): ?array
{
    $requestor = (string) sess('requestor', '');
    $map = [
        '1' => ['em_number', (string) sess('txt_emnumber', '')],
        '2' => ['stu_id',    (string) sess('no_stud', '')],
        '3' => ['ic_no',     (string) sess('no_kp', '')],
    ];
    if (!isset($map[$requestor]) || trim($map[$requestor][1]) === '') {
        return null;
    }
    return $map[$requestor];
}

/**
 * Pastikan aduan wr_id milik pengguna semasa.
 * Pulang baris wr (kolum $cols) jika milik, null jika tiada/bukan milik.
 */
function fetchOwnWr(int $wr_id, string $cols = 'wr_id'): ?array
{
    $owner = currentOwner();
    if ($wr_id <= 0 || $owner === null) {
        return null;
    }
    [$col, $val] = $owner;
    return dbFetchOne("SELECT {$cols} FROM wr WHERE wr_id = ? AND {$col} = ?", [$wr_id, $val]);
}

/**
 * Label untuk Medium (kolum type_wr dalam jadual wr).
 * Ikut sistem eAduan lama sepenuhnya supaya paparan konsisten:
 * 1=Online, 2=Surat, 3=Telefon, 4=Inisiatif PPF,
 * 5=Borang ADKRS, 6=Memo, 7=E-mail, 8=Minit Mesyuarat.
 */
function getMediumLabels(): array
{
    return [
        '1' => 'Online',
        '2' => 'Surat',
        '3' => 'Telefon',
        '4' => 'Inisiatif PPF',
        '5' => 'Borang ADKRS',
        '6' => 'Memo',
        '7' => 'E-mail',
        '8' => 'Minit Mesyuarat',
    ];
}

/** Papar deskripsi jenis kerosakan: tanda / diganti dengan - */
function descSlashToDash(?string $s): string
{
    return str_replace('/', ' - ', trim($s ?? ''));
}

/** Papar kod dan deskripsi: "KOD - Deskripsi" */
function formatCodeDesc(?string $code, ?string $desc): string
{
    $code = trim($code ?? '');
    $desc = trim($desc ?? '');
    if ($code !== '' && $desc !== '') {
        if (strcasecmp($code, $desc) === 0) {
            return $desc;
        }
        return $code . ' - ' . $desc;
    }
    return $desc !== '' ? $desc : $code;
}

/** Label status aduan (kod → deskripsi penuh) */
function getWrStatusMap(): array
{
    return [
        'R'   => ['label' => 'PERMOHONAN',                                           'badge' => 'badge-status-r'],
        'I'   => ['label' => 'ARAHAN KERJA DIKELUARKAN',                             'badge' => 'badge-status-w'],
        'APP' => ['label' => 'DILULUSKAN',                                           'badge' => 'badge-status-w'],
        'W'   => ['label' => 'DALAM TINDAKAN',                                       'badge' => 'badge-status-w'],
        'COM' => ['label' => 'SIAP',                                                 'badge' => 'badge-status-com'],
        'S'   => ['label' => 'SIAP',                                                 'badge' => 'badge-status-com'],
        'CAN' => ['label' => 'DIBATALKAN',                                            'badge' => 'badge-status-can'],
        'REJ' => ['label' => 'DITOLAK',                                               'badge' => 'badge-status-can'],
        'A'   => ['label' => 'DITOLAK',                                               'badge' => 'badge-status-can'],
        'CLS' => ['label' => 'DITUTUP',                                               'badge' => 'badge-status-com'],
        'CLO' => ['label' => 'DITUTUP',                                               'badge' => 'badge-status-com'],
        'PEN' => ['label' => 'PENDING',                                               'badge' => 'badge-status-r'],
        'HLD' => ['label' => 'DITANGGUH',                                             'badge' => 'badge-status-r'],
        'REV' => ['label' => 'DISEMAK SEMULA DAN OPERASI DIHENTIKAN SEMENTARA',        'badge' => 'badge-status-r'],
        'AA'  => ['label' => 'WORK ORDER DIKELUARKAN',                                'badge' => 'badge-status-w'],
        'HP'  => ['label' => 'OPERASI DIHENTIKAN SEMENTARA MENUNGGU PERALATAN',        'badge' => 'badge-status-r'],
        'HA'  => ['label' => 'OPERASI DIHENTIKAN SEMENTARA MENUNGGU KEBENARAN MASUK', 'badge' => 'badge-status-r'],
        'HL'  => ['label' => 'OPERASI DIHENTIKAN SEMENTARA MENUNGGU PEKERJA',         'badge' => 'badge-status-r'],
        'WC'  => ['label' => 'SALAH SALURAN',                                         'badge' => 'badge-status-can'],
        'DEF' => ['label' => 'KECACATAN DALAM TEMPOH TANGGUNGAN',                     'badge' => 'badge-status-r'],
    ];
}

/** Dapatkan label & badge status daripada kod */
function getWrStatusInfo(string $statusCode): array
{
    $st = strtoupper(trim($statusCode));
    if ($st === '') {
        $st = 'COM';
    }
    $map = getWrStatusMap();
    return $map[$st] ?? ['label' => $st, 'badge' => 'bg-secondary'];
}
