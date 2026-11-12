<?php
/**
 * eAduan v3  Landing / Login Page
 * Revamp: Centered login with Menara SAAS campus background
 * Dev: Mohammad Zurairi | XDIBAX Innovation
 */
session_start();
require_once __DIR__ . '/includes/functions.php';

if (!empty($_SESSION['sso_authenticated'])) {
    header('Location: ' . baseUrl() . '/pages/borang.php');
    exit;
}

$cfg = getAuthConfig();

$return = urlencode(baseUrl() . '/auth/sso_callback.php');
$ssoUrlStaf    = $cfg['portal_url'] . '?return=' . $return;
$ssoUrlPelajar = $cfg['portal_url'] . '?return=' . $return;

$googleUrlStaf    = baseUrl() . '/auth/google_callback.php?type=staf';
$googleUrlPelajar = baseUrl() . '/auth/google_callback.php?type=student';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>e-Aduan v3  Sistem Aduan UiTM</title>

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#5B2D8E">
    <meta name="description" content="Sistem pengurusan aduan fasiliti Universiti Teknologi MARA">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="e-Aduan">
    <meta name="application-name" content="e-Aduan v3">
    <meta name="msapplication-TileColor" content="#5B2D8E">
    <meta name="msapplication-TileImage" content="<?php echo baseUrl(); ?>/assets/icons/icon-144x144.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo baseUrl(); ?>/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo baseUrl(); ?>/assets/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo baseUrl(); ?>/assets/icons/icon-192x192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous" rel="stylesheet">

    <link rel="preload" as="image" href="<?php echo baseUrl(); ?>/assets/images/login-menara-saas.jpg">
    <link rel="stylesheet" href="<?php echo baseUrl(); ?>/assets/css/login.css?v=20261112-2">
</head>
<body>

<div class="login-shell">
    <header class="site-header">
        <a class="site-brand" href="<?php echo baseUrl(); ?>/" aria-label="e-Aduan v3, Universiti Teknologi MARA">
            <span class="uitm-crest"><img src="<?php echo baseUrl(); ?>/assets/images/logo-uitm.png" alt="Logo UiTM" width="204" height="88"></span>
            <span class="site-university">UNIVERSITI<br>TEKNOLOGI<br>MARA</span>
            <span class="site-brand-name">e-Aduan Fasiliti v3</span>
        </a>
        <details class="login-help">
            <summary>Bantuan</summary>
            <div class="help-content">
                <strong>Perlukan bantuan log masuk?</strong>
                <p>Staf dan pelajar: gunakan akaun Google UiTM anda.</p>
                <p>Pengguna luar: pilih Pengguna Luar untuk mendaftar atau log masuk.</p>
                <p>Untuk memasang aplikasi, tekan Muat Turun e-Aduan.</p>
            </div>
        </details>
    </header>

    <div class="campus-message">
        <h1>Fasiliti terjaga.<br><strong>Kampus sejahtera.</strong></h1>
        <span aria-hidden="true"></span>
    </div>

    <main class="login-main">
    <section class="login-card" aria-labelledby="login-heading">
        <div class="card-brand">
            <span class="uitm-crest"><img src="<?php echo baseUrl(); ?>/assets/images/logo-uitm.png" alt="Logo UiTM" width="204" height="88"></span>
            <div class="card-brand-title">e-Aduan Fasiliti v3</div>
            <p>Universiti Teknologi MARA</p>
        </div>

        <h2 id="login-heading" class="form-heading">Selamat Datang</h2>
        <p class="form-sub">Urus aduan fasiliti kampus anda dengan mudah.</p>

        <!-- Alerts -->
        <?php if (!empty($_GET['logout'])): ?>
        <div class="login-alert login-alert-success">
            <i class="bi bi-check-circle-fill"></i>
            Anda telah berjaya log keluar.
        </div>
        <?php endif; ?>
        <?php if (!empty($_GET['expired'])): ?>
        <div class="login-alert login-alert-warning">
            <i class="bi bi-clock-history"></i>
            Sesi anda telah tamat. Sila log masuk semula.
        </div>
        <?php endif; ?>

        <!-- Login Options -->
        <div class="login-label"><span>Staf &amp; Pelajar</span></div>

        <a href="<?php echo $googleUrlStaf; ?>" class="login-btn btn-google">
            <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59a14.5 14.5 0 0 1 0-9.18l-7.98-6.19a24.01 24.01 0 0 0 0 21.56l7.98-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Log masuk dengan Google
        </a>

        <div class="login-divider"><span>atau</span></div>

        <a href="<?php echo baseUrl(); ?>/pages/daftar_luar.php" class="login-btn btn-luar">
            <i class="bi bi-person-lines-fill"></i>
            Pengguna Luar
        </a>

        <!-- PWA Install Section -->
        <div id="pwa-install-section" class="pwa-install-section">
            <div class="pwa-install-divider" aria-hidden="true"></div>
            <button id="pwa-install-main-btn" class="login-btn btn-pwa-install" type="button">
                <i class="bi bi-download"></i> Muat Turun e-Aduan
            </button>
            <p class="pwa-install-hint" id="pwa-install-hint"></p>
        </div>

        <p class="login-trust"><i class="bi bi-lock" aria-hidden="true"></i> Akses selamat melalui akaun anda</p>
    </section>
    </main>

    <footer class="site-footer">
        &copy; <?php echo date('Y'); ?> Universiti Teknologi MARA <span aria-hidden="true">&middot;</span> Jabatan Pembangunan
    </footer>
</div>

<script src="<?php echo baseUrl(); ?>/assets/js/pwa-register.js"></script>
<script src="<?php echo baseUrl(); ?>/assets/js/pwa-install.js"></script>
<script>
/* ── Console Marker ── */
(function () {
    var style = [
        'background: linear-gradient(135deg, #5B2D8E, #3D1A6E)',
        'color: #FFFFFF',
        'padding: 6px 14px',
        'border-radius: 4px',
        'font-family: Inter, sans-serif',
        'font-size: 12px',
        'font-weight: 700',
        'letter-spacing: 0.08em',
    ].join(';');
    var styleGold = [
        'background: transparent',
        'color: #D4A843',
        'font-family: Inter, sans-serif',
        'font-size: 11px',
        'font-weight: 500',
    ].join(';');
    console.log('%c e-Aduan v3 %c', style, 'background:transparent');
    console.log('%c Developer  : Mohammad Zurairi', styleGold);
    console.log('%c System     : ea_newv3 | XDIBAX Innovation', styleGold);
    console.log('%c Build      : ' + new Date().getFullYear() + ' @ UiTM', styleGold);
})();
</script>
</body>
</html>
