<?php
/**
 * eAduan v3 – Landing Page (Split-screen)
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
    <title>e-Aduan v3 – Sistem Aduan UiTM</title>

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

    <!-- PWA Icons -->
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo baseUrl(); ?>/assets/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?php echo baseUrl(); ?>/assets/icons/icon-512x512.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo baseUrl(); ?>/assets/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo baseUrl(); ?>/assets/icons/icon-192x192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root {
        --uitm-purple: #5B2D8E;
        --uitm-purple-dark: #3D1A6E;
        --uitm-purple-light: #7B4FAE;
        --uitm-gold: #D4A843;
        --uitm-gold-light: #F5E6C0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

    .login-wrapper {
        display: flex;
        min-height: 100vh;
    }

    /* ---- LEFT PANEL: Photo + overlay ---- */
    .login-photo {
        flex: 1;
        position: relative;
        background:
            linear-gradient(135deg, rgba(59,26,110,0.82) 0%, rgba(91,45,142,0.72) 50%, rgba(123,79,174,0.65) 100%),
            url('<?php echo baseUrl(); ?>/assets/images/bg-login.png')
            center/cover no-repeat;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 48px;
    }
    .login-photo-text {
        color: rgba(255,255,255,0.92);
    }
    .login-photo-text h2 {
        font-size: 2rem;
        font-weight: 800;
        margin-bottom: 8px;
        letter-spacing: -0.02em;
    }
    .login-photo-text p {
        font-size: 1rem;
        font-weight: 400;
        opacity: 0.8;
        max-width: 380px;
        line-height: 1.5;
    }
    .login-photo-dots {
        position: absolute;
        top: 40px;
        left: 40px;
        display: grid;
        grid-template-columns: repeat(4, 8px);
        gap: 10px;
    }
    .login-photo-dots span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255,255,255,0.25);
    }

    /* ---- RIGHT PANEL: Login form ---- */
    .login-form {
        width: 480px;
        min-width: 380px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 48px 40px;
        background: #fff;
        overflow-y: auto;
    }
    .login-form-inner {
        width: 100%;
        max-width: 340px;
    }

    /* Logo */
    .login-logo {
        text-align: center;
        margin-bottom: 28px;
    }
    .login-logo-img {
        height: 72px;
        width: auto;
        margin-bottom: 14px;
        object-fit: contain;
    }
    .login-logo h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--uitm-purple-dark);
        margin-bottom: 2px;
    }
    .login-logo p {
        font-size: 0.85rem;
        color: #888;
        font-weight: 400;
    }

    /* Alerts */
    .login-alert {
        border-left: 3px solid;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 0.82rem;
        margin-bottom: 20px;
    }
    .login-alert-success { border-color: #198754; background: #f0fdf4; color: #166534; }
    .login-alert-warning { border-color: var(--uitm-gold); background: #fffbeb; color: #92400e; }

    /* Section label */
    .login-label {
        text-align: center;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #aaa;
        margin-bottom: 18px;
    }

    /* Buttons */
    .login-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 13px 20px;
        border-radius: 10px;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
        border: none;
    }
    .login-btn:hover {
        transform: translateY(-1px);
    }

    .login-btn-staf {
        background: linear-gradient(135deg, var(--uitm-purple), var(--uitm-purple-dark));
        color: #fff;
        margin-bottom: 10px;
    }
    .login-btn-staf:hover {
        box-shadow: 0 4px 16px rgba(91,45,142,0.35);
        color: #fff;
    }

    .login-btn-pelajar {
        background: var(--uitm-purple-light);
        color: #fff;
        margin-bottom: 10px;
    }
    .login-btn-pelajar:hover {
        box-shadow: 0 4px 16px rgba(123,79,174,0.35);
        color: #fff;
    }

    .login-btn-google {
        background: #fff;
        color: #444;
        border: 1.5px solid #ddd;
        margin-bottom: 10px;
    }
    .login-btn-google:hover {
        border-color: #4285F4;
        box-shadow: 0 2px 10px rgba(66,133,244,0.15);
        color: #333;
    }
    .login-btn-google svg { flex-shrink: 0; }

    .login-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 14px 0;
        color: #ccc;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
    .login-divider::before,
    .login-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e8e8e8;
    }

    .login-btn-luar {
        background: #fff;
        color: var(--uitm-gold);
        border: 2px solid var(--uitm-gold);
    }
    .login-btn-luar:hover {
        background: var(--uitm-gold);
        color: #fff;
        box-shadow: 0 4px 14px rgba(212,168,67,0.3);
    }

    /* Footer */
    .login-footer {
        text-align: center;
        margin-top: 32px;
        font-size: 0.72rem;
        color: #bbb;
        line-height: 1.6;
    }
    .login-footer strong { color: #999; }

    /* Gold strip */
    .login-gold-strip {
        position: absolute;
        top: 0;
        right: 0;
        width: 480px;
        height: 3px;
        background: linear-gradient(90deg, var(--uitm-gold), var(--uitm-gold-light), var(--uitm-gold));
        z-index: 2;
    }

    /* ---- PWA INSTALL SECTION ---- */
    .pwa-install-section {
        margin-top: 20px;
    }
    .pwa-install-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 14px;
        color: #bbb;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }
    .pwa-install-divider::before,
    .pwa-install-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e8e8e8;
    }
    .pwa-install-divider span {
        white-space: nowrap;
    }
    .pwa-install-btn {
        background: linear-gradient(135deg, #1a8754, #157347);
        color: #fff;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .pwa-install-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
        animation: pwa-shimmer 2.5s infinite;
    }
    @keyframes pwa-shimmer {
        0%   { left: -100%; }
        100% { left: 100%; }
    }
    .pwa-install-btn:hover {
        box-shadow: 0 4px 16px rgba(25,135,84,0.35);
        color: #fff;
    }
    .pwa-install-btn .bi {
        font-size: 1.1em;
    }
    .pwa-install-hint {
        text-align: center;
        font-size: 0.72rem;
        color: #999;
        margin-top: 8px;
        line-height: 1.5;
    }
    .pwa-install-hint .bi {
        vertical-align: -1px;
    }
    .pwa-installed-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 10px;
        background: #f0fdf4;
        border: 1.5px solid #bbf7d0;
        color: #166534;
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* ---- iOS MODAL ---- */
    .pwa-ios-overlay {
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 16px;
        animation: pwa-fade-in 0.2s;
    }
    @keyframes pwa-fade-in {
        from { opacity: 0; } to { opacity: 1; }
    }
    .pwa-ios-modal {
        background: #fff;
        border-radius: 16px;
        padding: 28px 24px;
        max-width: 380px;
        width: 100%;
        text-align: center;
        box-shadow: 0 -8px 40px rgba(0,0,0,0.15);
        animation: pwa-slide-up 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes pwa-slide-up {
        from { transform: translateY(40px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
    .pwa-ios-modal h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--uitm-purple-dark);
        margin-bottom: 16px;
    }
    .pwa-ios-step {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.85rem;
        color: #333;
    }
    .pwa-ios-step:last-child { border-bottom: none; }
    .pwa-ios-step-num {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--uitm-purple);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .pwa-ios-close {
        margin-top: 16px;
        background: none;
        border: 1.5px solid #ddd;
        border-radius: 10px;
        padding: 10px 24px;
        font-family: inherit;
        font-size: 0.85rem;
        font-weight: 600;
        color: #666;
        cursor: pointer;
        transition: background 0.15s;
    }
    .pwa-ios-close:hover { background: #f5f5f5; }

    /* ---- RESPONSIVE ---- */
    @media (max-width: 900px) {
        .login-photo { display: none; }
        .login-form {
            width: 100%;
            min-width: unset;
            min-height: 100vh;
        }
        .login-gold-strip { width: 100%; }
    }
    @media (max-width: 480px) {
        .login-form { padding: 32px 24px; }
        .login-form-inner { max-width: 100%; }
    }
    </style>
</head>
<body>

<div class="login-wrapper">
    <!-- LEFT: Photo panel -->
    <div class="login-photo">
        <div class="login-photo-dots">
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
            <span></span><span></span><span></span><span></span>
        </div>
        <div class="login-photo-text">
            <h2>e-Aduan v3</h2>
            <p>Sistem pengurusan aduan fasiliti Universiti Teknologi MARA. Laporkan kerosakan dengan mudah dan pantau status pembaikan secara dalam talian.</p>
        </div>
    </div>

    <!-- RIGHT: Login form panel -->
    <div class="login-form">
        <div class="login-gold-strip"></div>
        <div class="login-form-inner">

            <div class="login-logo">
                <img src="<?php echo baseUrl(); ?>/assets/images/logo-uitm.png" alt="UiTM" class="login-logo-img">
                <h1>e-Aduan v3</h1>
                <p>Universiti Teknologi MARA</p>
            </div>

            <?php if (!empty($_GET['logout'])): ?>
            <div class="login-alert login-alert-success">
                <i class="bi bi-check-circle-fill"></i> Anda telah berjaya log keluar.
            </div>
            <?php endif; ?>
            <?php if (!empty($_GET['expired'])): ?>
            <div class="login-alert login-alert-warning">
                <i class="bi bi-clock-history"></i> Sesi anda telah tamat. Sila log masuk semula.
            </div>
            <?php endif; ?>

            <!-- <div class="login-label">Log Masuk Sebagai</div>

            <a href="<?php echo $ssoUrlStaf; ?>" class="login-btn login-btn-staf">
                <i class="bi bi-person-badge-fill"></i> Staf UiTM
            </a>

            <a href="<?php echo $ssoUrlPelajar; ?>" class="login-btn login-btn-pelajar">
                <i class="bi bi-mortarboard-fill"></i> Pelajar UiTM
            </a> -->

            <a href="<?php echo $googleUrlStaf; ?>" class="login-btn login-btn-google">
                <svg width="20" height="20" viewBox="0 0 48 48"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59a14.5 14.5 0 0 1 0-9.18l-7.98-6.19a24.01 24.01 0 0 0 0 21.56l7.98-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                Log masuk dengan Google
            </a>

            <div class="login-divider">atau</div>

            <a href="<?php echo baseUrl(); ?>/pages/daftar_luar.php" class="login-btn login-btn-luar">
                <i class="bi bi-person-lines-fill"></i> Pengguna Luar
            </a>

            <!-- PWA Install Section -->
            <div id="pwa-install-section" class="pwa-install-section">
                <div class="pwa-install-divider">
                    <span>Pasang Aplikasi</span>
                </div>
                <button id="pwa-install-main-btn" class="login-btn pwa-install-btn" type="button">
                    <i class="bi bi-download"></i> Pasang e-Aduan
                </button>
                <p class="pwa-install-hint" id="pwa-install-hint"></p>
            </div>

            <div class="login-footer">
                <strong>e-Aduan v3</strong><br>
                &copy; <?php echo date('Y'); ?> Bahagian Pengurusan Fasiliti<br>
                Universiti Teknologi MARA
            </div>

        </div>
    </div>
</div>

<script src="<?php echo baseUrl(); ?>/assets/js/pwa-register.js"></script>
<script src="<?php echo baseUrl(); ?>/assets/js/pwa-install.js"></script>
</body>
</html>
