<?php
declare(strict_types=1);

/**
 * eAduan v3 – Portal PPF: aduan bagi pihak pekerja (Terima Aduan & Inisiatif Jabatan).
 * Operator PPF kekal log masuk sebagai dirinya; pekerja terpilih disimpan dalam slot sesi
 * `ppf_proxy` (ditetapkan server selepas guard + semakan pekerja) dan hanya digunakan bila
 * permintaan membawa ppf=1. Kategori pengadu sentiasa inisiatif (type_req=5) — ditetapkan dalam simpan.php.
 */
require_once __DIR__ . '/ppf_guard.php';

const PPF_PROXY_TTL = 1800;
const PPF_PROXY_MEDIUM = ['2', '3', '5', '6', '7', '8'];

/** Mulakan slot untuk pekerja; pulang null jika berjaya, atau mesej ralat. */
function ppfProxyStart(string $emNumber, string $medium): ?string
{
    if (!preg_match('/^\d{1,12}$/', $emNumber)) {
        return 'No. Pekerja tidak sah.';
    }
    if (!in_array($medium, PPF_PROXY_MEDIUM, true)) {
        return 'Medium aduan tidak sah.';
    }
    $em = dbFetchOne("SELECT em_number, status FROM em WHERE em_number = ?", [$emNumber]);
    if (!$em) {
        return 'No. Pekerja tidak dijumpai.';
    }
    if (strtoupper(trim((string) $em['status'])) === 'BERHENTI') {
        return 'Pekerja ini telah berhenti.';
    }
    $_SESSION['ppf_proxy'] = [
        'em_number' => trim((string) $em['em_number']),
        'medium'    => $medium,
        'operator'  => trim((string) sess('txt_emnumber', '')),
        'exp'       => time() + PPF_PROXY_TTL,
    ];
    return null;
}

function ppfProxyRequested(): bool
{
    return (($_POST['ppf'] ?? $_GET['ppf'] ?? '') === '1');
}

/** Slot sah (belum tamat, operator masih lulus guard PPF/DFMS + intranet) atau null. */
function ppfProxyActive(): ?array
{
    $p = $_SESSION['ppf_proxy'] ?? null;
    if (!is_array($p) || (int) ($p['exp'] ?? 0) < time()) {
        unset($_SESSION['ppf_proxy']);
        return null;
    }
    if (ppfAccessCheck()['status'] !== 200) {
        return null;
    }
    $_SESSION['ppf_proxy']['exp'] = time() + PPF_PROXY_TTL;
    return $p;
}

/**
 * Identiti pelapor berkesan. Tanpa ppf=1: nilai sesi seperti biasa. Dengan ppf=1: pekerja terpilih,
 * atau (jika slot tamat/tidak sah) hentikan permintaan — JANGAN jatuh balik ke identiti operator.
 */
function reporterIdentity(string $defaultRequestor = '', bool $popup = false): array
{
    if (!ppfProxyRequested()) {
        return [
            'requestor'    => (string) sess('requestor', $defaultRequestor),
            'txt_emnumber' => (string) sess('txt_emnumber', ''),
            'no_stud'      => (string) sess('no_stud', ''),
            'no_kp'        => (string) sess('no_kp', ''),
            'proxy'        => false,
            'medium'       => '',
            'operator'     => '',
        ];
    }
    $p = ppfProxyActive();
    if ($p === null) {
        ppfSendNoStore();
        if ($popup) {
            http_response_code(403);
            exit('Sesi borang pekerja tamat. Sila mulakan semula daripada Portal PPF.');
        }
        header('Location: ' . baseUrl() . '/pages/ppf.php#terima-aduan');
        exit;
    }
    return [
        'requestor'    => '1',
        'txt_emnumber' => (string) $p['em_number'],
        'no_stud'      => '',
        'no_kp'        => '',
        'proxy'        => true,
        'medium'       => (string) $p['medium'],
        'operator'     => (string) $p['operator'],
    ];
}
