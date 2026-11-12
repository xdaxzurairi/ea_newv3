<?php
/**
 * eAduan v3 – Terima Kasih
 * Halaman selepas aduan berjaya dihantar.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Terima Kasih';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ea-card">
    <div class="card-body p-5 text-center">
        <div class="mb-3">
            <i class="bi bi-emoji-smile" style="font-size:3.5rem;color:var(--uitm-purple);"></i>
        </div>
        <h4 class="fw-bold" style="color:var(--uitm-purple-dark);">Terima Kasih!</h4>
        <p class="text-muted mb-1">Aduan anda telah berjaya direkodkan dalam sistem e-Aduan.</p>
        <p class="text-muted mb-4" style="font-size:0.85rem;">
            Pihak Bahagian Pengurusan Fasiliti akan mengambil tindakan sewajarnya.
            Anda boleh menyemak status aduan pada bila-bila masa.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?php echo baseUrl(); ?>/pages/borang.php" class="btn btn-uitm-gold px-4">
                <i class="bi bi-plus-circle"></i> Buat Aduan Baru
            </a>
            <a href="<?php echo baseUrl(); ?>/pages/semak.php" class="btn btn-uitm px-4">
                <i class="bi bi-search"></i> Semak Aduan
            </a>
            <?php if (!empty($_SESSION['sso_authenticated'])): ?>
            <a href="<?php echo baseUrl(); ?>/auth/logout.php" class="btn btn-outline-secondary px-4">
                <i class="bi bi-box-arrow-right"></i> Log Keluar
            </a>
            <?php else: ?>
            <a href="<?php echo baseUrl(); ?>/index.php" class="btn btn-outline-secondary px-4">
                <i class="bi bi-house"></i> Halaman Utama
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
