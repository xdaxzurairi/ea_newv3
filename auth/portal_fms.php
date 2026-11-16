<?php
/**
 * eAduan v3 – jambatan log masuk daripada portalFMS.
 * portalFMS dan eAduanV2 berkongsi sesi PHP (PHPSESSID, hos sama); portalFMS menandakan log masuk dengan
 * $_SESSION['isLoggedIn'] + ['uid'], eAduanV2 memerlukan ['sso_authenticated']. Halaman ini menukar yang pertama
 * kepada yang kedua selepas mengesahkan uid sebagai staf AKTIF dalam jadual em.
 *
 * Lalai MATI. Aktif dengan PORTAL_FMS_BRIDGE=1 dalam .env.php.
 * Pautan portalFMS: /eAduanV2/auth/portal_fms.php?next=borang|ppf|semak (lalai borang).
 */
require_once __DIR__ . '/../includes/session_init.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/env.php';

$targets = ['borang' => '/pages/borang.php', 'ppf' => '/pages/ppf.php', 'semak' => '/pages/semak.php'];
$next    = is_string($_GET['next'] ?? null) ? $_GET['next'] : 'borang';
$dest    = baseUrl() . ($targets[$next] ?? $targets['borang']);
$login   = baseUrl() . '/index.php';

if (getenv('PORTAL_FMS_BRIDGE') !== '1'
    || ($_SESSION['isLoggedIn'] ?? false) !== true
    || !empty($_SESSION['is_mock'])) {
    header('Location: ' . $login);
    exit;
}

$uid = trim((string) ($_SESSION['uid'] ?? ''));
$em  = $uid !== '' ? dbFetchOne("SELECT em_number, em_id, email, status FROM em WHERE em_number = ?", [$uid]) : null;
if (!$em || strtoupper(trim((string) $em['status'])) !== 'AKTIF') {
    header('Location: ' . $login);
    exit;
}

$emNumber = trim((string) $em['em_number']);
$already  = ($_SESSION['sso_authenticated'] ?? false) === true
    && (string) ($_SESSION['requestor'] ?? '') === '1'
    && (string) ($_SESSION['txt_emnumber'] ?? '') === $emNumber
    && (empty($_SESSION['token_exp']) || $_SESSION['token_exp'] > time());

if (!$already) {
    $_SESSION['requestor']    = '1';
    $_SESSION['txt_emnumber'] = $emNumber;
    $_SESSION['no_stud']      = '';
    $_SESSION['no_kp']        = '';
    $_SESSION['user_id']      = $emNumber;
    $_SESSION['user_name']    = trim((string) $em['em_id']);
    regenerateLoginSession();
    $_SESSION['sso_authenticated'] = true;
    $_SESSION['user_email'] = trim((string) ($em['email'] ?? ''));
    $_SESSION['user_type']  = 'staf';
    $_SESSION['login_time'] = time();
    $_SESSION['token_exp']  = time() + 7200;
}

header('Location: ' . $dest);
exit;
