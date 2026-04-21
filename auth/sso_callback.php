<?php
/**
 * Portal Warga SSO Callback (JWT)
 * Rujukan: eAduan/portal_warga_sso.php
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

$cfg = getAuthConfig();

// Dapatkan JWT token
$jwt = $_POST['token'] ?? $_GET['token'] ?? null;
if (!$jwt && isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) {
        $jwt = $m[1];
    }
}

// Jika sudah authenticated, redirect terus
if (!$jwt && !empty($_SESSION['sso_authenticated']) && ($_SESSION['token_exp'] ?? 0) > time()) {
    header('Location: ' . baseUrl() . '/pages/borang.php');
    exit;
}

// Tiada token -> redirect ke Portal Warga
if (empty($jwt)) {
    $return = urlencode(baseUrl() . '/auth/sso_callback.php');
    header('Location: ' . $cfg['portal_url'] . '?return=' . $return);
    exit;
}

// Decode JWT
$payload = decodeJWT($jwt, $cfg['jwt_secret']);
if ($payload === false) {
    session_destroy();
    session_start();
    $return = urlencode(baseUrl() . '/auth/sso_callback.php');
    header('Location: ' . $cfg['portal_url'] . '?return=' . $return);
    exit;
}

// Validate required fields
foreach (['type_user', 'iduitm', 'email'] as $field) {
    if (empty($payload[$field])) {
        die('Token tidak lengkap. Field diperlukan: type_user, iduitm, email');
    }
}
if (empty($payload['name'])) {
    $payload['name'] = ucfirst(explode('@', $payload['email'])[0]);
}

// Map type_user ke requestor & validate dalam DB
$pdo = getPdo();
$userType = strtolower($payload['type_user']);

if ($userType === 'staf') {
    $row = dbFetchOne("SELECT em_number FROM em WHERE em_number = ?", [$payload['iduitm']]);
    if (!$row) { die('No. staf tidak dijumpai dalam sistem.'); }
    $_SESSION['requestor']     = '1';
    $_SESSION['txt_emnumber']  = $payload['iduitm'];
    $_SESSION['no_stud']       = '';
    $_SESSION['no_kp']         = '';
} elseif ($userType === 'student') {
    $row = dbFetchOne("SELECT stu_id FROM stu WHERE stu_id = ?", [$payload['iduitm']]);
    if (!$row) { die('No. pelajar tidak dijumpai dalam sistem.'); }
    $_SESSION['requestor']     = '2';
    $_SESSION['txt_emnumber']  = '';
    $_SESSION['no_stud']       = $payload['iduitm'];
    $_SESSION['no_kp']         = '';
} else {
    die('Jenis pengguna tidak disokong melalui SSO. Sila gunakan pendaftaran Pengguna Luar.');
}

$_SESSION['sso_authenticated'] = true;
$_SESSION['user_id']    = $payload['iduitm'];
$_SESSION['user_email'] = $payload['email'];
$_SESSION['user_name']  = $payload['name'];
$_SESSION['user_type']  = $userType;
$_SESSION['login_time'] = time();
$_SESSION['token_exp']  = $payload['exp'] ?? (time() + 7200);

header('Location: ' . baseUrl() . '/pages/borang.php');
exit;

// ---- JWT Functions ----

function decodeJWT(string $jwt, string $secret)
{
    $parts = explode('.', $jwt);
    if (count($parts) !== 3) return false;

    [$header64, $payload64, $signature64] = $parts;
    $header  = json_decode(base64UrlDec($header64), true);
    $payload = json_decode(base64UrlDec($payload64), true);
    if (!$header || !$payload) return false;

    $alg = strtoupper($header['alg'] ?? 'HS512');
    $hash = match ($alg) {
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512',
        default => null,
    };
    if (!$hash) return false;

    $sig = base64UrlDec($signature64);
    $expected = hash_hmac($hash, "$header64.$payload64", $secret, true);
    if (!hash_equals($sig, $expected)) return false;

    if (isset($payload['exp']) && $payload['exp'] < time()) return false;

    return $payload;
}

function base64UrlDec(string $input): string
{
    $pad = strlen($input) % 4;
    if ($pad) $input .= str_repeat('=', 4 - $pad);
    return base64_decode(strtr($input, '-_', '+/'));
}
