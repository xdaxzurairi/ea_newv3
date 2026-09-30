<?php
declare(strict_types=1);

/**
 * eAduan v3 – OTP e-mel untuk log masuk Pengguna Luar.
 * Kod disimpan sebagai HMAC dalam sesi; throttle penghantaran ikut No. K/P
 * disimpan dalam fail temp pelayan (tiada perubahan schema DB).
 */

const OTP_TTL          = 600; // 10 minit
const OTP_MAX_ATTEMPTS = 5;
const OTP_COOLDOWN     = 60;  // saat antara permintaan kod
const OTP_MAX_PER_HOUR = 5;

/** Laluan fail throttle untuk satu No. K/P */
function otpThrottleFile(string $ic): ?string
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'eaduan_otp';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
        error_log('otp: gagal cipta direktori throttle ' . $dir);
        return null;
    }
    return $dir . DIRECTORY_SEPARATOR . hash('sha256', strtoupper($ic)) . '.json';
}

/**
 * Semak had penghantaran untuk No. K/P. Pulang mesej ralat, atau null jika dibenarkan.
 * Jika dibenarkan, rekod penghantaran ini.
 */
function otpThrottleHit(string $ic): ?string
{
    $file = otpThrottleFile($ic);
    if ($file === null) {
        return null; // fail-open: OTP masih wajib, cuma throttle tidak aktif
    }
    $now   = time();
    $raw   = @file_get_contents($file);
    $times = array_filter(
        $raw ? (array) json_decode($raw, true) : [],
        fn($t) => is_int($t) && $t > $now - 3600
    );
    rsort($times);

    if ($times && $now - $times[0] < OTP_COOLDOWN) {
        return 'Sila tunggu ' . (OTP_COOLDOWN - ($now - $times[0])) . ' saat sebelum meminta kod baru.';
    }
    if (count($times) >= OTP_MAX_PER_HOUR) {
        return 'Terlalu banyak permintaan kod. Sila cuba semula dalam masa sejam.';
    }
    $times[] = $now;
    @file_put_contents($file, json_encode(array_values($times)), LOCK_EX);
    return null;
}

/** Sembunyikan sebahagian e-mel: zurairi@uitm.edu.my → z*****i@uitm.edu.my */
function otpMaskEmail(string $email): string
{
    [$user, $domain] = explode('@', $email, 2) + ['', ''];
    $len = strlen($user);
    $masked = $len <= 2
        ? str_repeat('*', $len)
        : $user[0] . str_repeat('*', $len - 2) . $user[$len - 1];
    return $masked . '@' . $domain;
}

/**
 * Jana kod baru, simpan dalam $_SESSION['otp_pending'] dan hantar e-mel.
 * $pending: ic, name, tel, email, is_new. Pulang mesej ralat, atau null jika berjaya.
 */
function otpIssue(array $pending): ?string
{
    if ($err = otpThrottleHit($pending['ic'])) {
        return $err;
    }

    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $salt = bin2hex(random_bytes(16));

    $subject = 'e-Aduan UiTM: Kod Pengesahan Log Masuk';
    $body  = "<html><body style='font-family:Arial,sans-serif;font-size:14px;'>";
    $body .= "<p>Assalamualaikum w.b.t dan selamat sejahtera,</p>";
    $body .= "<p>Kod pengesahan untuk log masuk e-Aduan UiTM anda ialah:</p>";
    $body .= "<p style='font-size:28px;font-weight:bold;letter-spacing:6px;color:#5B2D8E;'>{$code}</p>";
    $body .= "<p>Kod ini sah selama " . (OTP_TTL / 60) . " minit. Jangan kongsi kod ini dengan sesiapa.</p>";
    $body .= "<p>Jika anda tidak meminta kod ini, sila abaikan e-mel ini.</p>";
    $body .= "<hr style='border:none;border-top:2px solid #D4A843;'>";
    $body .= "<p style='font-size:12px;color:#888;'>E-mel ini dijana secara automatik oleh Sistem e-Aduan v3, UiTM.</p>";
    $body .= "</body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: eAduan <jpem@uitm.edu.my>\r\n";

    if (!@mail($pending['email'], $subject, $body, $headers)) {
        error_log('otp: mail() gagal untuk ' . otpMaskEmail($pending['email']));
        return 'Gagal menghantar e-mel pengesahan. Sila cuba sebentar lagi.';
    }

    $_SESSION['otp_pending'] = $pending + [
        'salt'     => $salt,
        'hash'     => hash_hmac('sha256', $code, $salt),
        'exp'      => time() + OTP_TTL,
        'attempts' => 0,
    ];
    return null;
}

/**
 * Sahkan kod. Pulang data pending jika betul; mesej ralat (string) jika salah.
 * Kod dibuang dari sesi apabila berjaya, tamat tempoh, atau cubaan habis.
 */
function otpVerify(string $code): array|string
{
    $p = $_SESSION['otp_pending'] ?? null;
    if (!is_array($p) || empty($p['hash'])) {
        return 'Tiada kod aktif. Sila minta kod baru.';
    }
    if (time() > $p['exp']) {
        unset($_SESSION['otp_pending']['hash']);
        return 'Kod telah tamat tempoh. Sila minta kod baru.';
    }

    if (preg_match('/^\d{6}$/', $code) && hash_equals($p['hash'], hash_hmac('sha256', $code, $p['salt']))) {
        unset($_SESSION['otp_pending']);
        return $p;
    }

    $_SESSION['otp_pending']['attempts'] = ++$p['attempts'];
    if ($p['attempts'] >= OTP_MAX_ATTEMPTS) {
        unset($_SESSION['otp_pending']['hash']);
        return 'Terlalu banyak cubaan salah. Sila minta kod baru.';
    }
    return 'Kod tidak sah. Baki cubaan: ' . (OTP_MAX_ATTEMPTS - $p['attempts']) . '.';
}
