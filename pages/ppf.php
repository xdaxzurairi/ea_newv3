<?php
/**
 * eAduan v3 – Portal PPF (dashboard baca-sahaja). Guard sebelum sebarang output.
 * Rangka HTML: includes/ppf-dashboard.html; data: ajax/ppf_dashboard.php.
 */
require_once __DIR__ . '/../includes/ppf_guard.php';

ppfEnforce('page');

$pageTitle = 'Portal PPF';
$base = baseUrl();

$fragment = (string) file_get_contents(__DIR__ . '/../includes/ppf-dashboard.html');
$root = '<section id="ppf-dashboard" class="ppf-shell"';
$attrs = ' data-endpoint="' . e($base . '/ajax/ppf_dashboard.php') . '"'
    . ' data-detail-url="' . e($base . '/pages/ppf_detail.php') . '"'
    . ' data-login-url="' . e($base . '/index.php') . '"';
$fragment = str_replace($root, $root . $attrs, $fragment);

require_once __DIR__ . '/../includes/header.php';
?>
<link href="<?php echo e($base); ?>/assets/css/ppf.css" rel="stylesheet">
<?php echo $fragment; ?>
<script src="<?php echo e($base); ?>/assets/js/ppf-dashboard.js" defer></script>
<script src="<?php echo e($base); ?>/assets/js/ppf-feedback.js" defer></script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
