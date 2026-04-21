<?php
/**
 * Google OAuth 2.0 Callback (Dev/Test)
 * Map email Google ke em_number (staf) atau stu_id (pelajar) dalam DB.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

$cfg = getAuthConfig();
$clientId     = $cfg['google_client_id'];
$clientSecret = $cfg['google_client_secret'];
$redirectUri  = $cfg['google_redirect_uri'];

// Jika tiada code, redirect ke Google consent screen
if (empty($_GET['code'])) {
    $type = $_GET['type'] ?? 'staf'; // 'staf' atau 'student'
    $_SESSION['google_login_type'] = $type;

    $params = http_build_query([
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'access_type'   => 'offline',
        'prompt'        => 'select_account',
    ]);
    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    exit;
}

// Tukar code ke access token
$tokenResp = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code'          => $_GET['code'],
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));

if (!$tokenResp) {
    die('Gagal mendapatkan token dari Google.');
}
$tokenData = json_decode($tokenResp, true);
$accessToken = $tokenData['access_token'] ?? '';
if (!$accessToken) {
    die('Access token tidak sah.');
}

// Dapatkan user info
$userInfo = json_decode(
    file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, stream_context_create([
        'http' => ['header' => "Authorization: Bearer $accessToken"],
    ])),
    true
);

$email = $userInfo['email'] ?? '';
$name  = $userInfo['name'] ?? ucfirst(explode('@', $email)[0]);

if (!$email) {
    die('Gagal mendapatkan email dari Google.');
}

// Map email ke DB
$pdo = getPdo();
$type = $_SESSION['google_login_type'] ?? 'staf';
unset($_SESSION['google_login_type']);

if ($type === 'staf') {
    $row = dbFetchOne("SELECT em_number, em_id FROM em WHERE email = ?", [$email]);
    if (!$row) {
        echo '<p>Email <strong>' . e($email) . '</strong> tidak dijumpai dalam jadual staf (em).</p>';
        echo '<p><a href="' . baseUrl() . '/index.php">Kembali</a></p>';
        exit;
    }
    $_SESSION['requestor']    = '1';
    $_SESSION['txt_emnumber'] = $row['em_number'];
    $_SESSION['no_stud']      = '';
    $_SESSION['no_kp']        = '';
    $_SESSION['user_id']      = $row['em_number'];
    $_SESSION['user_name']    = $row['em_id'] ?? $name;
} else {
    $row = dbFetchOne("SELECT stu_id, stu_name FROM stu WHERE email = ?", [$email]);
    if (!$row) {
        echo '<p>Email <strong>' . e($email) . '</strong> tidak dijumpai dalam jadual pelajar (stu).</p>';
        echo '<p><a href="' . baseUrl() . '/index.php">Kembali</a></p>';
        exit;
    }
    $_SESSION['requestor']    = '2';
    $_SESSION['txt_emnumber'] = '';
    $_SESSION['no_stud']      = $row['stu_id'];
    $_SESSION['no_kp']        = '';
    $_SESSION['user_id']      = $row['stu_id'];
    $_SESSION['user_name']    = $row['stu_name'] ?? $name;
}

$_SESSION['sso_authenticated'] = true;
$_SESSION['user_email'] = $email;
$_SESSION['user_type']  = $type;
$_SESSION['login_time'] = time();
$_SESSION['token_exp']  = time() + 7200;

header('Location: ' . baseUrl() . '/pages/borang.php');
exit;
