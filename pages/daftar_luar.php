<?php
/**
 * Pendaftaran / Log Masuk Pengguna Luar (dengan OTP e-mel)
 * Langkah 1: masukkan No. K/P (+ maklumat jika pengguna baru) → kod dihantar ke e-mel.
 *   - KP sudah ada dalam afm_user_osd → kod ke e-mel berdaftar (tiada e-mel = hubungi pentadbir).
 *   - KP belum ada → kod ke e-mel yang diisi; rekod hanya di-INSERT selepas disahkan.
 * Langkah 2: masukkan kod 6 digit → sesi dibuka.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/otp.php';

$error  = '';
$notice = '';
$action = post('action');

if (($_GET['batal'] ?? '') === '1') {
    unset($_SESSION['otp_pending']);
    header('Location: ' . baseUrl() . '/pages/daftar_luar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();

    if ($action === 'request') {
        $ic_no     = post('ic_no');
        $name_user = post('name_user');
        $tel_no    = post('tel_no');
        $email     = post('email');

        if ($ic_no === '' || !preg_match('/^[A-Za-z0-9\-]{6,20}$/', $ic_no)) {
            $error = 'Sila masukkan No. Kad Pengenalan / Pasport yang sah.';
        } else {
            $row = dbFetchOne(
                "SELECT ic_no, name_user, tel_no, email FROM afm_user_osd WHERE ic_no = ?",
                [$ic_no]
            );

            if ($row) {
                // Pengguna sedia ada — kod WAJIB ke e-mel berdaftar sahaja
                $regEmail = trim($row['email'] ?? '');
                if (!filter_var($regEmail, FILTER_VALIDATE_EMAIL)) {
                    $error = 'Tiada e-mel sah didaftarkan untuk No. K/P ini. Sila hubungi pentadbir e-Aduan untuk mengemas kini e-mel anda.';
                } else {
                    $error = otpIssue([
                        'ic'     => trim($row['ic_no']),
                        'name'   => trim($row['name_user'] ?? ''),
                        'tel'    => trim($row['tel_no'] ?? ''),
                        'email'  => $regEmail,
                        'is_new' => false,
                    ]) ?? '';
                }
            } elseif (!preg_match('/^.{1,100}$/u', $name_user)) {
                $error = 'Pengguna baru: sila masukkan nama penuh (maksimum 100 aksara).';
            } elseif (!preg_match('/^[0-9+\-\s]{0,20}$/', $tel_no)) {
                $error = 'No. telefon tidak sah.';
            } elseif (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Pengguna baru: sila masukkan e-mel yang sah untuk menerima kod pengesahan.';
            } else {
                $error = otpIssue([
                    'ic'     => $ic_no,
                    'name'   => $name_user,
                    'tel'    => $tel_no,
                    'email'  => $email,
                    'is_new' => true,
                ]) ?? '';
            }
        }

    } elseif ($action === 'resend' && !empty($_SESSION['otp_pending'])) {
        $p = $_SESSION['otp_pending'];
        $error = otpIssue(array_intersect_key($p, array_flip(['ic', 'name', 'tel', 'email', 'is_new']))) ?? '';
        if ($error === '') {
            $notice = 'Kod baru telah dihantar.';
        }

    } elseif ($action === 'verify') {
        $result = otpVerify(post('otp'));

        if (is_string($result)) {
            $error = $result;
        } else {
            $ic_no = $result['ic'];
            if ($result['is_new'] && !dbFetchOne("SELECT ic_no FROM afm_user_osd WHERE ic_no = ?", [$ic_no])) {
                dbExecute(
                    "INSERT INTO afm_user_osd (name_user, ic_no, tel_no, email) VALUES (?, ?, ?, ?)",
                    [$result['name'], $ic_no, $result['tel'] ?: null, $result['email']]
                );
            }

            regenerateLoginSession();
            $_SESSION['sso_authenticated'] = true;
            $_SESSION['requestor']    = '3';
            $_SESSION['txt_emnumber'] = '';
            $_SESSION['no_stud']      = '';
            $_SESSION['no_kp']        = $ic_no;
            $_SESSION['user_id']      = $ic_no;
            $_SESSION['user_name']    = $result['name'];
            $_SESSION['user_email']   = $result['email'];
            $_SESSION['user_type']    = 'luar';
            $_SESSION['login_time']   = time();
            $_SESSION['token_exp']    = time() + 7200;

            header('Location: ' . baseUrl() . '/pages/borang.php');
            exit;
        }
    }
}

// Langkah 2 dipapar selagi ada kod aktif dalam sesi
$pending    = $_SESSION['otp_pending'] ?? null;
$showVerify = is_array($pending) && !empty($pending['hash']);
$canResend  = is_array($pending) && !empty($pending['email']);

$pageTitle = 'Pendaftaran Pengguna Luar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="ea-card">
            <div class="ea-sec-hdr">
                <i class="bi bi-person-plus"></i> <?php echo $canResend ? 'Pengesahan E-mel' : 'Pendaftaran Pengguna Luar'; ?>
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

                <?php if ($canResend): ?>
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    Kod pengesahan 6 digit telah dihantar ke <strong><?php echo e(otpMaskEmail($pending['email'])); ?></strong>.
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
                    <a href="?batal=1" class="btn btn-uitm-gold btn-sm"><i class="bi bi-arrow-left"></i> Tukar No. K/P</a>
                </form>

                <?php else: ?>
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    Jika anda pernah membuat aduan sebelum ini, masukkan No. K/P sahaja – kod pengesahan akan dihantar ke e-mel berdaftar anda.
                    Pengguna baru sila lengkapkan semua maklumat.
                </p>

                <form method="post" class="ea-form">
                    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
                    <input type="hidden" name="action" value="request">
                    <div class="mb-3">
                        <label class="form-label">No. Kad Pengenalan <span class="required">*</span></label>
                        <input type="text" name="ic_no" class="form-control" required maxlength="20"
                               value="<?php echo e(post('ic_no')); ?>"
                               placeholder="Contoh: 880101145577">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Penuh <small class="text-muted">(pengguna baru)</small></label>
                        <input type="text" name="name_user" class="form-control" maxlength="100"
                               value="<?php echo e(post('name_user')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telefon</label>
                        <input type="text" name="tel_no" class="form-control" maxlength="20"
                               value="<?php echo e(post('tel_no')); ?>"
                               placeholder="Contoh: 0123456789">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mel <small class="text-muted">(pengguna baru — untuk kod pengesahan)</small></label>
                        <input type="email" name="email" class="form-control" maxlength="100"
                               value="<?php echo e(post('email')); ?>">
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
