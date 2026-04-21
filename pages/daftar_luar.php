<?php
/**
 * Pendaftaran / Log Masuk Pengguna Luar
 * Jika KP sudah ada dalam afm_user_osd → guna data sedia ada.
 * Jika belum → INSERT baru.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_user = post('name_user');
    $ic_no     = post('ic_no');
    $tel_no    = post('tel_no');
    $email     = post('email');

    if ($ic_no === '') {
        $error = 'Sila masukkan No. Kad Pengenalan.';
    } elseif ($name_user === '') {
        $error = 'Sila masukkan nama penuh.';
    } else {
        // Semak jika sudah ada
        $row = dbFetchOne(
            "SELECT ic_no, name_user, tel_no, email FROM afm_user_osd WHERE ic_no = ?",
            [$ic_no]
        );

        if ($row) {
            // Sudah ada – guna data sedia ada
            $name_user = $row['name_user'];
            $tel_no    = $row['tel_no'] ?? $tel_no;
            $email     = $row['email'] ?? $email;
        } else {
            // Belum ada – INSERT baru
            dbExecute(
                "INSERT INTO afm_user_osd (name_user, ic_no, tel_no, email) VALUES (?, ?, ?, ?)",
                [$name_user, $ic_no, $tel_no ?: null, $email ?: null]
            );
        }

        // Set session
        $_SESSION['sso_authenticated'] = true;
        $_SESSION['requestor']    = '3';
        $_SESSION['txt_emnumber'] = '';
        $_SESSION['no_stud']      = '';
        $_SESSION['no_kp']        = $ic_no;
        $_SESSION['user_id']      = $ic_no;
        $_SESSION['user_name']    = $name_user;
        $_SESSION['user_email']   = $email;
        $_SESSION['user_type']    = 'luar';
        $_SESSION['login_time']   = time();
        $_SESSION['token_exp']    = time() + 7200;

        header('Location: ' . baseUrl() . '/pages/borang.php');
        exit;
    }
}

$pageTitle = 'Pendaftaran Pengguna Luar';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <div class="ea-card">
            <div class="ea-sec-hdr">
                <i class="bi bi-person-plus"></i> Pendaftaran Pengguna Luar
            </div>
            <div class="card-body p-4">
                <p class="text-secondary mb-3" style="font-size:0.85rem;">
                    Sila isi maklumat di bawah. Jika anda pernah membuat aduan sebelum ini, masukkan No. K/P sahaja – data anda akan dipaparkan secara automatik.
                </p>

                <?php if ($error): ?>
                <div class="alert alert-danger py-2" style="font-size:0.85rem;">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo e($error); ?>
                </div>
                <?php endif; ?>

                <form method="post" class="ea-form">
                    <div class="mb-3">
                        <label class="form-label">No. Kad Pengenalan <span class="required">*</span></label>
                        <input type="text" name="ic_no" class="form-control" required
                               value="<?php echo e(post('ic_no')); ?>"
                               placeholder="Contoh: 880101145577">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Penuh <span class="required">*</span></label>
                        <input type="text" name="name_user" class="form-control" required
                               value="<?php echo e(post('name_user')); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telefon</label>
                        <input type="text" name="tel_no" class="form-control"
                               value="<?php echo e(post('tel_no')); ?>"
                               placeholder="Contoh: 0123456789">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mel</label>
                        <input type="email" name="email" class="form-control"
                               value="<?php echo e(post('email')); ?>">
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-uitm"><i class="bi bi-arrow-right-circle"></i> Teruskan ke Borang Aduan</button>
                        <a href="<?php echo baseUrl(); ?>/index.php" class="btn btn-uitm-gold"><i class="bi bi-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
