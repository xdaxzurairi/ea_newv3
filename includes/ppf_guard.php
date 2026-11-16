<?php
declare(strict_types=1);

/**
 * eAduan v3 – guard Portal PPF: sesi sah + staf AKTIF (em) yang berdaftar dalam afm_users dengan role_name tidak kosong
 * (sama seperti capaian portal PPF eAduan lama / kad menu portalFMS) + rangkaian intranet.
 * Gagal tertutup: tanpa PPF_INTRANET_CIDR dalam persekitaran, semua akses ditolak (403).
 *
 * Persekitaran (.env.php):
 *   PPF_INTRANET_CIDR     senarai CIDR/IP dipisah koma, contoh "10.0.0.0/8,172.16.0.0/12"
 *   PPF_ALLOW_INTERNET    (sementara) "1" = langkau semakan intranet; sesi + staf aktif berperanan tetap dikuatkuasa
 *   PPF_ALLOW_USERS       (sementara) no. staf dipisah koma yang melangkau semakan intranet; staf aktif berperanan tetap diperlukan
 *   PPF_TRUSTED_PROXIES   (pilihan) CIDR/IP proksi yang dipercayai untuk X-Forwarded-For
 */
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/env.php';

function ppfSendNoStore(): void
{
    if (PHP_SAPI !== 'cli' && !headers_sent()) {
        header('Cache-Control: no-store, private');
        header('Pragma: no-cache');
    }
}

/** @return string[] */
function ppfEnvList(string $name): array
{
    $raw = (string) (getenv($name) ?: '');
    return array_values(array_filter(array_map('trim', explode(',', $raw)), 'strlen'));
}

function ppfIpInCidr(string $ip, string $cidr): bool
{
    $bits = null;
    if (strpos($cidr, '/') !== false) {
        [$cidr, $maskStr] = explode('/', $cidr, 2);
        if (!ctype_digit($maskStr)) {
            return false;
        }
        $bits = (int) $maskStr;
    }
    $ipBin  = @inet_pton($ip);
    $netBin = @inet_pton($cidr);
    if ($ipBin === false || $netBin === false || strlen($ipBin) !== strlen($netBin)) {
        return false;
    }
    $max = strlen($ipBin) * 8;
    $bits ??= $max;
    if ($bits < 0 || $bits > $max) {
        return false;
    }
    $bytes = intdiv($bits, 8);
    if ($bytes > 0 && substr($ipBin, 0, $bytes) !== substr($netBin, 0, $bytes)) {
        return false;
    }
    $rem = $bits % 8;
    if ($rem === 0) {
        return true;
    }
    $mask = (0xFF << (8 - $rem)) & 0xFF;
    return (ord($ipBin[$bytes]) & $mask) === (ord($netBin[$bytes]) & $mask);
}

function ppfIpInAny(string $ip, array $cidrs): bool
{
    foreach ($cidrs as $cidr) {
        if (ppfIpInCidr($ip, $cidr)) {
            return true;
        }
    }
    return false;
}

/** IP klien: REMOTE_ADDR; X-Forwarded-For hanya dipercayai jika REMOTE_ADDR ialah proksi yang disenaraikan. */
function ppfClientIp(): string
{
    $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $proxies = ppfEnvList('PPF_TRUSTED_PROXIES');
    if ($remote === '' || !$proxies || !ppfIpInAny($remote, $proxies)) {
        return $remote;
    }
    $hops = array_reverse(array_map('trim', explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))));
    foreach ($hops as $hop) {
        if (filter_var($hop, FILTER_VALIDATE_IP) === false) {
            return $remote;
        }
        if (!ppfIpInAny($hop, $proxies)) {
            return $hop;
        }
    }
    return $remote;
}

function ppfIsIntranet(): bool
{
    if (getenv('PPF_ALLOW_INTERNET') === '1') {
        return true;
    }
    $emNumber = trim((string) sess('txt_emnumber', ''));
    if ($emNumber !== '' && in_array($emNumber, ppfEnvList('PPF_ALLOW_USERS'), true)) {
        return true;
    }
    $cidrs = ppfEnvList('PPF_INTRANET_CIDR');
    $ip = ppfClientIp();
    return $cidrs && $ip !== '' && ppfIpInAny($ip, $cidrs);
}

function ppfIsPpfStaff(): bool
{
    if ((string) sess('requestor', '') !== '1') {
        return false;
    }
    $emNumber = trim((string) sess('txt_emnumber', ''));
    if ($emNumber === '') {
        return false;
    }
    try {
        $em = dbFetchOne("SELECT status FROM em WHERE em_number = ?", [$emNumber]);
        if (!$em || strtoupper(trim((string) $em['status'])) !== 'AKTIF') {
            return false;
        }
        return (bool) dbFetchColumn(
            "SELECT 1 FROM afm_users WHERE user_name = ? AND RTRIM(ISNULL(role_name, '')) <> ''",
            [$emNumber]
        );
    } catch (Throwable $e) {
        error_log('ppf_guard: semakan akses PPF gagal: ' . $e->getMessage());
        return false;
    }
}

/** @return array{status:int,error:string,reason:string} status 200 jika dibenarkan */
function ppfAccessCheck(): array
{
    if (empty($_SESSION['sso_authenticated']) || $_SESSION['sso_authenticated'] !== true) {
        return ['status' => 401, 'error' => 'session_expired', 'reason' => ''];
    }
    if (!empty($_SESSION['token_exp']) && $_SESSION['token_exp'] < time()) {
        return ['status' => 401, 'error' => 'session_expired', 'reason' => ''];
    }
    if (!ppfIsIntranet()) {
        return ['status' => 403, 'error' => 'access_denied', 'reason' => 'intranet_required'];
    }
    if (!ppfIsPpfStaff()) {
        return ['status' => 403, 'error' => 'access_denied', 'reason' => 'ppf_required'];
    }
    return ['status' => 200, 'error' => '', 'reason' => ''];
}

/** Boolean untuk menu; tiada kesan sampingan. */
function ppfCanAccess(): bool
{
    static $can = null;
    return $can ??= (ppfAccessCheck()['status'] === 200);
}

/**
 * Kuatkuasa akses sebelum sebarang output. mode: 'page' (HTML), 'json', 'plain' (tanpa badan, cth foto).
 * Sentiasa Cache-Control: no-store, private.
 */
function ppfEnforce(string $mode): void
{
    ppfSendNoStore();
    $r = ppfAccessCheck();
    if ($r['status'] === 200) {
        return;
    }

    if ($r['status'] === 401 && !empty($_SESSION['token_exp']) && $_SESSION['token_exp'] < time()) {
        session_destroy();
    }

    http_response_code($r['status']);

    if ($mode === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        $body = ['ok' => false, 'error' => $r['error']];
        if ($r['reason'] !== '') {
            $body['reason'] = $r['reason'];
        }
        echo json_encode($body);
        exit;
    }

    if ($mode === 'plain') {
        exit;
    }

    if ($r['status'] === 401) {
        header('Location: ' . baseUrl() . '/index.php?expired=1');
        exit;
    }

    $pageTitle = $r['reason'] === 'intranet_required' ? 'Akses rangkaian dalaman diperlukan' : 'Akses ditolak';
    $msg = $r['reason'] === 'intranet_required'
        ? 'Portal PPF hanya boleh diakses melalui rangkaian dalaman UiTM.'
        : 'Portal PPF hanya untuk staf PPF.';
    require_once __DIR__ . '/header.php';
    echo '<div class="ea-card"><div class="card-body p-4 text-center">';
    echo '<i class="bi bi-shield-lock" style="font-size:2.5rem;color:#dc3545;"></i>';
    echo '<h1 class="h5 mt-2">' . e($pageTitle) . '</h1>';
    echo '<p class="mb-0">' . e($msg) . '</p>';
    echo '<a href="' . e(baseUrl()) . '/pages/borang.php" class="btn btn-uitm mt-3"><i class="bi bi-arrow-left"></i> Kembali</a>';
    echo '</div></div>';
    require_once __DIR__ . '/footer.php';
    exit;
}
