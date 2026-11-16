<?php
/**
 * eAduan v3 – Portal PPF: pintu masuk borang aduan penuh bagi pihak pekerja.
 * GET txt_emnumber=<no. pekerja>&medium=<2|3|5|6|7|8> -> sahkan akses + pekerja, kemudian ke borang.php?ppf=1.
 */
require_once __DIR__ . '/../includes/ppf_proxy.php';

ppfEnforce('page');

$emNumber = is_string($_GET['txt_emnumber'] ?? null) ? trim($_GET['txt_emnumber']) : '';
$medium   = is_string($_GET['medium'] ?? null) ? trim($_GET['medium']) : '';

$err = ppfProxyStart($emNumber, $medium);
if ($err === null) {
    header('Location: ' . baseUrl() . '/pages/borang.php?ppf=1');
    exit;
}

http_response_code(400);
$pageTitle = 'Terima Aduan';
require_once __DIR__ . '/../includes/header.php';
echo '<div class="ea-card"><div class="card-body p-4 text-center">';
echo '<i class="bi bi-exclamation-circle" style="font-size:2.5rem;color:#dc3545;"></i>';
echo '<h1 class="h5 mt-2">Borang tidak dapat dibuka</h1>';
echo '<p class="mb-0">' . e($err) . '</p>';
echo '<a href="' . e(baseUrl()) . '/pages/ppf.php#terima-aduan" class="btn btn-uitm mt-3"><i class="bi bi-arrow-left"></i> Kembali</a>';
echo '</div></div>';
require_once __DIR__ . '/../includes/footer.php';
