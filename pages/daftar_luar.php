<?php
/**
 * Log Masuk / Pendaftaran Pengguna Luar (OTP e-mel)
 * Langkah 1: masukkan e-mel → kod 6 digit dihantar ke e-mel tersebut.
 * Langkah 2: sahkan kod (bukti pemilik e-mel), kemudian:
 *   - e-mel berdaftar dengan 1 akaun → terus log masuk
 *   - e-mel berdaftar dengan >1 akaun (data lama) → pilih akaun
 *   - e-mel belum berdaftar → borang pengguna baru (nama, No. K/P, tel);
 *     rekod afm_user_osd hanya di-INSERT selepas e-mel disahkan.
 * No. K/P kekal sebagai kunci pengguna luar dalam wr/afm_user_osd.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/otp.php';

const LUAR_VERIFIED_TTL = 900; // 15 minit untuk pilih akaun / lengkapkan pendaftaran

/** Semua akaun pengguna luar yang didaftarkan dengan e-mel ini */
function luarAccountsByEmail(string $email): array
{
    return dbFetchAll(
        "SELECT ic_no, name_user, tel_no, email FROM afm_user_osd
         WHERE LOWER(LTRIM(RTRIM(email))) = ? ORDER BY name_user",
        [strtolower(trim($email))]
    );
}

/** 880414086093 → ********6093 */
function maskIc(string $ic): string
{
    $ic = trim($ic);
    return strlen($ic) <= 4 ? $ic : str_repeat('*', strlen($ic) - 4) . substr($ic, -4);
}

/** Buka sesi pengguna luar dan ke borang aduan */
function loginLuar(string $ic, string $name, string $email): void
{
    unset($_SESSION['otp_pending'], $_SESSION['luar_verified']);
    regenerateLoginSession();
    $_SESSION['sso_authenticated'] = true;
    $_SESSION['requestor']    = '3';
    $_SESSION['txt_emnumber'] = '';
    $_SESSION['no_stud']      = '';
    $_SESSION['no_kp']        = $ic;
    $_SESSION['user_id']      = $ic;
    $_SESSION['user_name']    = $name;
    $_SESSION['user_email']   = $email;
    $_SESSION['user_type']    = 'luar';
    $_SESSION['login_time']   = time();
    $_SESSION['token_exp']    = time() + 7200;
    header('Location: ' . baseUrl() . '/pages/borang.php');
    exit;
}

$error  = '';
$notice = '';
$action = post('action');

if (($_GET['batal'] ?? '') === '1') {
    unset($_SESSION['otp_pending'], $_SESSION['luar_verified']);
    header('Location: ' . baseUrl() . '/pages/daftar_luar.php');
    exit;
}

// E-mel yang telah disahkan melalui OTP (untuk pilih akaun / daftar baru)
$verified = $_SESSION['luar_verified'] ?? null;
if (is_array($verified) && time() > $verified['exp']) {
    unset($_SESSION['luar_verified']);
    $verified = null;
    $error = 'Sesi pengesahan telah tamat. Sila masukkan e-mel semula.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    if ($action === 'request') {
        $email = strtolower(post('email'));
        unset($_SESSION['otp_pending'], $_SESSION['luar_verified']);
        $verified = null;

        if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Sila masukkan alamat e-mel yang sah.';
        } else {
            $error = otpIssue(['email' => $email]) ?? '';
        }

    } elseif ($action === 'resend' && !empty($_SESSION['otp_pending']['email'])) {
        $error = otpIssue(['email' => $_SESSION['otp_pending']['email']]) ?? '';
        if ($error === '') {
            $notice = 'Kod baru telah dihantar.';
        }

    } elseif ($action === 'verify') {
        $result = otpVerify(post('otp'));

        if (is_string($result)) {
            $error = $result;
        } else {
            $email    = $result['email'];
            $accounts = luarAccountsByEmail($email);
            if (count($accounts) === 1) {
                $a = $accounts[0];
                loginLuar(trim($a['ic_no']), trim($a['name_user'] ?? ''), $email);
            }
            // 0 akaun → daftar baru; >1 akaun → pilih akaun
            $_SESSION['luar_verified'] = ['email' => $email, 'exp' => time() + LUAR_VERIFIED_TTL];
            $verified = $_SESSION['luar_verified'];
        }

    } elseif ($action === 'pick' && $verified) {
        $pick = post('ic_no');
        foreach (luarAccountsByEmail($verified['email']) as $a) {
            if (trim($a['ic_no']) === $pick) {
                loginLuar($pick, trim($a['name_user'] ?? ''), $verified['email']);
            }
        }
        $error = 'Akaun tidak sah. Sila pilih semula.';

    } elseif ($action === 'register' && $verified) {
        $email     = $verified['email'];
        $name_user = post('name_user');
        $ic_no     = post('ic_no');
        $tel_no    = post('tel_no');

        if (luarAccountsByEmail($email)) {
            $error = 'E-mel ini telah berdaftar. Sila log masuk semula.';
            unset($_SESSION['luar_verified']);
            $verified = null;
        } elseif (!preg_match('/^.{1,100}$/u', $name_user)) {
            $error = 'Sila masukkan nama penuh (maksimum 100 aksara).';
        } elseif (!preg_match('/^[A-Za-z0-9\-]{6,20}$/', $ic_no)) {
            $error = 'Sila masukkan No. Kad Pengenalan / Pasport yang sah.';
        } elseif (!preg_match('/^[0-9+\-\s]{0,20}$/', $tel_no)) {
            $error = 'No. telefon tidak sah.';
        } elseif (dbFetchOne("SELECT ic_no FROM afm_user_osd WHERE ic_no = ?", [$ic_no])) {
            // Jangan benarkan tuntut akaun sedia ada dengan e-mel lain
            $error = 'No. K/P ini telah berdaftar dengan e-mel lain. Sila log masuk menggunakan e-mel berdaftar, atau hubungi pentadbir e-Aduan.';
        } else {
            dbExecute(
                "INSERT INTO afm_user_osd (name_user, ic_no, tel_no, email) VALUES (?, ?, ?, ?)",
                [$name_user, $ic_no, $tel_no ?: null, $email]
            );
            loginLuar($ic_no, $name_user, $email);
        }
    }
}

// Tentukan skrin
$pending    = $_SESSION['otp_pending'] ?? null;
$showCode   = is_array($pending) && !empty($pending['email']) && !$verified;
$showVerify = $showCode && !empty($pending['hash']);
$accounts   = $verified ? luarAccountsByEmail($verified['email']) : [];
$showPick   = $verified && count($accounts) > 1;
$showReg    = $verified && !$accounts;

$title = $showPick ? 'Pilih Akaun' : ($showReg ? 'Pendaftaran Pengguna Baru' : ($showCode ? 'Pengesahan E-mel' : 'Log Masuk Pengguna Luar'));

$pageTitle = 'Log Masuk Pengguna Luar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="ea-card">
            <div class="ea-sec-hdr">
                <i class="bi bi-person-plus"></i> <?php echo e($title); ?>
            </div>
            <div class="card-body p-4">

                <?php if ($error): ?>
                <div class="alert alert-danger py-2" style="font-size:0.85rem;">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo e($error); ?>
                </div>
                <?php endif; ?>
                <?php if ($notice): ?>
                <div class="alert alert-success py-2" style="font-size:0.85rem;">
                    <i class="bi bi-check-circle"></i> <?php echo e($notice); ?>
                </div>
                <?php endif; ?>

                <?php if ($showPick): ?>
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    E-mel <strong><?php echo e($verified['email']); ?></strong> didaftarkan untuk beberapa akaun. Sila pilih akaun anda:
                </p>
                <form method="post" class="d-grid gap-2">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="action" value="pick">
                    <?php foreach ($accounts as $a): ?>
                    <button type="submit" name="ic_no" value="<?php echo e(trim($a['ic_no'])); ?>" class="btn btn-outline-secondary text-start">
                        <i class="bi bi-person-circle"></i>
                        <strong><?php echo e(trim($a['name_user'] ?? '-')); ?></strong>
                        <span class="text-muted ms-1">(No. K/P <?php echo e(maskIc($a['ic_no'])); ?>)</span>
                    </button>
                    <?php endforeach; ?>
                    <a href="?batal=1" class="btn btn-uitm-gold btn-sm mt-2"><i class="bi bi-arrow-left"></i> Tukar E-mel</a>
                </form>

                <?php elseif ($showReg): ?>
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    E-mel <strong><?php echo e($verified['email']); ?></strong> telah disahkan tetapi belum berdaftar.
                    Sila lengkapkan maklumat di bawah — pendaftaran ini dibuat sekali sahaja.
                </p>
                <form method="post" class="ea-form">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="action" value="register">
                    <div class="mb-3">
                        <label class="form-label">Nama Penuh <span class="required">*</span></label>
                        <input type="text" name="name_user" class="form-control" required maxlength="100" autofocus
                               value="<?php echo e(post('name_user')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Kad Pengenalan / Pasport <span class="required">*</span></label>
                        <input type="text" name="ic_no" class="form-control" required maxlength="20"
                               value="<?php echo e(post('ic_no')); ?>"
                               placeholder="Contoh: 880101145577">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telefon</label>
                        <input type="text" name="tel_no" class="form-control" maxlength="20"
                               value="<?php echo e(post('tel_no')); ?>"
                               placeholder="Contoh: 0123456789">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-uitm"><i class="bi bi-person-check"></i> Daftar & Teruskan</button>
                        <a href="?batal=1" class="btn btn-uitm-gold"><i class="bi bi-arrow-left"></i> Tukar E-mel</a>
                    </div>
                </form>

                <?php elseif ($showCode): ?>
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    Kod pengesahan 6 digit telah dihantar ke <strong><?php echo e($pending['email']); ?></strong>.
                    Kod sah selama <?php echo OTP_TTL / 60; ?> minit. Sila semak juga folder spam.
                </p>

                <?php if ($showVerify): ?>
                <form method="post" class="ea-form mb-2">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="action" value="verify">
                    <div class="mb-3">
                        <label class="form-label">Kod Pengesahan <span class="required">*</span></label>
                        <input type="text" name="otp" class="form-control text-center fs-4" required
                               inputmode="numeric" pattern="\d{6}" maxlength="6" autocomplete="one-time-code"
                               autofocus placeholder="••••••" style="letter-spacing:8px;">
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-uitm"><i class="bi bi-shield-check"></i> Sahkan & Teruskan</button>
                    </div>
                </form>
                <?php endif; ?>

                <form method="post" class="d-grid gap-2">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="action" value="resend">
                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-repeat"></i> Hantar Semula Kod</button>
                    <a href="?batal=1" class="btn btn-uitm-gold btn-sm"><i class="bi bi-arrow-left"></i> Tukar E-mel</a>
                </form>

                <?php else: ?>
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    Masukkan e-mel anda. Kod pengesahan akan dihantar ke e-mel tersebut.
                    Jika e-mel belum berdaftar, anda akan diminta mendaftar selepas pengesahan.
                </p>
                <form method="post" class="ea-form">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="action" value="request">
                    <div class="mb-3">
                        <label class="form-label">E-mel <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required maxlength="100" autofocus
                               autocomplete="email" value="<?php echo e(post('email')); ?>"
                               placeholder="nama@contoh.com">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-uitm"><i class="bi bi-envelope"></i> Hantar Kod Pengesahan</button>
                        <a href="<?php echo baseUrl(); ?>/index.php" class="btn btn-uitm-gold"><i class="bi bi-arrow-left"></i> Kembali</a>
                    </div>
                </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
