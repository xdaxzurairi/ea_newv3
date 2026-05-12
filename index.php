<?php
/**
 * eAduan v3  Landing / Login Page
 * Revamp: Clean White Minimalist Card
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    /*  Root Variables  */
    :root {
        --clr-purple:      #5B2D8E;
        --clr-purple-dk:   #3D1A6E;
        --clr-purple-lt:   #7B4FAE;
        --clr-purple-xs:   #F3EEFF;
        --clr-gold:        #D4A843;
        --clr-gold-lt:     #FBF0D5;
        --clr-bg:          #F7F5FF;
        --clr-card:        #FFFFFF;
        --clr-text:        #1E1532;
        --clr-muted:       #8B8499;
        --clr-border:      #E8E3F0;
        --radius-card:     24px;
        --shadow-card:     0 8px 48px rgba(91,45,142,0.10), 0 2px 12px rgba(91,45,142,0.06);
        --shadow-btn:      0 4px 16px rgba(91,45,142,0.25);
    }

    /*  Reset  */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        min-height: 100%;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 15px;
        color: var(--clr-text);
    }

    /*  Background  */
    body {
        background-color: var(--clr-bg);
        background-image:
            radial-gradient(ellipse 800px 600px at 10% 10%, rgba(91,45,142,0.07) 0%, transparent 70%),
            radial-gradient(ellipse 600px 500px at 90% 80%, rgba(212,168,67,0.06) 0%, transparent 70%);
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 24px 16px;
    }

    /*  Floating blob decorations  */
    body::before, body::after {
        content: '';
        position: fixed;
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }
    body::before {
        width: 360px; height: 360px;
        top: -80px; left: -80px;
        background: radial-gradient(circle, rgba(91,45,142,0.08) 0%, transparent 70%);
    }
    body::after {
        width: 280px; height: 280px;
        bottom: -60px; right: -60px;
        background: radial-gradient(circle, rgba(212,168,67,0.07) 0%, transparent 70%);
    }

    /*  Card  */
    .login-card {
        position: relative;
        z-index: 1;
        background: var(--clr-card);
        border-radius: var(--radius-card);
        box-shadow: var(--shadow-card);
        border: 1px solid var(--clr-border);
        width: 100%;
        max-width: 420px;
        padding: 48px 40px 40px;
        animation: card-in 0.45s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    @keyframes card-in {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Gold top accent */
    .login-card::before {
        content: '';
        position: absolute;
        top: 0; left: 40px; right: 40px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--clr-gold), var(--clr-gold-lt), var(--clr-gold), transparent);
        border-radius: 0 0 4px 4px;
    }

    /*  Logo section  */
    .login-logo {
        text-align: center;
        margin-bottom: 32px;
    }
    .login-logo img {
        height: 64px;
        width: auto;
        margin-bottom: 16px;
        object-fit: contain;
        filter: drop-shadow(0 2px 8px rgba(91,45,142,0.12));
    }
    .login-logo-title {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--clr-purple-dk);
        letter-spacing: -0.03em;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .login-logo-subtitle {
        font-size: 0.8rem;
        color: var(--clr-muted);
        font-weight: 400;
        letter-spacing: 0.01em;
    }

    /* ─ Section label  */
    .login-label {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }
    .login-label::before, .login-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--clr-border);
    }
    .login-label span {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: var(--clr-muted);
        white-space: nowrap;
    }

    /*  Alert banners  */
    .login-alert {
        border-radius: 10px;
        padding: 11px 14px;
        font-size: 0.8rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid;
    }
    .login-alert-success {
        background: #F0FDF6;
        border-color: #A7F3C3;
        color: #166534;
    }
    .login-alert-warning {
        background: var(--clr-gold-lt);
        border-color: rgba(212,168,67,0.4);
        color: #92400e;
    }

    /*  Buttons  */
    .login-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 13px 20px;
        border-radius: 12px;
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.2s ease;
        border: none;
        letter-spacing: -0.01em;
    }
    .login-btn + .login-btn { margin-top: 10px; }
    .login-btn:hover { transform: translateY(-1px); }
    .login-btn:active { transform: translateY(0); }
    .login-btn i { font-size: 1rem; }

    /* Google button */
    .btn-google {
        background: #FFFFFF;
        color: #3C3C3C;
        border: 1.5px solid var(--clr-border);
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .btn-google:hover {
        border-color: #4285F4;
        box-shadow: 0 2px 12px rgba(66,133,244,0.15);
        color: #1a1a1a;
    }
    .btn-google svg { flex-shrink: 0; }

    /* Pengguna Luar button */
    .btn-luar {
        background: var(--clr-gold-lt);
        color: #7A5C10;
        border: 1.5px solid rgba(212,168,67,0.4);
    }
    .btn-luar:hover {
        background: var(--clr-gold);
        color: #fff;
        border-color: var(--clr-gold);
        box-shadow: 0 4px 16px rgba(212,168,67,0.3);
    }

    /*  Divider  */
    .login-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 14px 0;
    }
    .login-divider::before, .login-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--clr-border);
    }
    .login-divider span {
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #C8C0D4;
        white-space: nowrap;
    }

    /*  PWA Section  */
    .pwa-install-section { margin-top: 6px; }
    .pwa-install-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 12px;
    }
    .pwa-install-divider::before, .pwa-install-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--clr-border);
    }
    .pwa-install-divider span {
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #C8C0D4;
        white-space: nowrap;
    }
    .btn-pwa-install {
        background: linear-gradient(135deg, #1a8754, #157347);
        color: #fff;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .btn-pwa-install::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
        transform: translateX(-100%);
        animation: shimmer 2.5s infinite;
    }
    @keyframes shimmer {
        to { transform: translateX(100%); }
    }
    .btn-pwa-install:hover {
        box-shadow: 0 4px 16px rgba(25,135,84,0.3);
        color: #fff;
    }
    .pwa-install-hint {
        text-align: center;
        font-size: 0.7rem;
        color: #B0A8C0;
        margin-top: 8px;
        line-height: 1.5;
    }
    .pwa-installed-badge {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border-radius: 12px;
        background: #F0FDF6;
        border: 1.5px solid #A7F3C3;
        color: #166534;
        font-size: 0.82rem;
        font-weight: 600;
    }

    /*  iOS Modal  */
    .pwa-ios-overlay {
        position: fixed;
        inset: 0;
        z-index: 10000;
        background: rgba(0,0,0,0.45);
        display: flex;
        align-items: flex-end;
        justify-content: center;
        padding: 16px;
        animation: fade-in 0.2s;
    }
    @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
    .pwa-ios-modal {
        background: #fff;
        border-radius: 20px;
        padding: 28px 24px;
        max-width: 380px;
        width: 100%;
        text-align: center;
        animation: slide-up 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes slide-up {
        from { transform: translateY(40px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
    .pwa-ios-modal h3 {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--clr-purple-dk);
        margin-bottom: 16px;
    }
    .pwa-ios-step {
        display: flex;
        align-items: center;
        gap: 12px;
        text-align: left;
        padding: 10px 0;
        border-bottom: 1px solid #F0F0F0;
        font-size: 0.82rem;
        color: #333;
    }
    .pwa-ios-step:last-child { border-bottom: none; }
    .pwa-ios-step-num {
        width: 26px; height: 26px;
        border-radius: 50%;
        background: var(--clr-purple);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.72rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .pwa-ios-close {
        margin-top: 16px;
        background: none;
        border: 1.5px solid #DDD;
        border-radius: 10px;
        padding: 10px 24px;
        font-family: inherit;
        font-size: 0.82rem;
        font-weight: 600;
        color: #666;
        cursor: pointer;
    }
    .pwa-ios-close:hover { background: #F5F5F5; }

    /*  Footer  */
    .login-footer {
        text-align: center;
        margin-top: 28px;
        padding-top: 20px;
        border-top: 1px solid var(--clr-border);
        font-size: 0.7rem;
        color: var(--clr-muted);
        line-height: 1.7;
    }
    .login-footer strong { color: #9B8ABB; }

    /*  Responsive  */
    @media (max-width: 480px) {
        .login-card { padding: 36px 24px 32px; }
    }
    </style>
</head>
<body>

<div class="login-card">

    <!-- Logo -->
    <div class="login-logo">
        <img src="<?php echo baseUrl(); ?>/assets/images/logo-uitm.png" alt="Logo UiTM">
        <div class="login-logo-title">e-Aduan v3</div>
        <div class="login-logo-subtitle">Universiti Teknologi MARA</div>
    </div>

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
    <div class="login-label"><span>Log Masuk</span></div>

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
        <div class="pwa-install-divider"><span>Pasang Aplikasi</span></div>
        <button id="pwa-install-main-btn" class="login-btn btn-pwa-install" type="button">
            <i class="bi bi-download"></i> Pasang e-Aduan
        </button>
        <p class="pwa-install-hint" id="pwa-install-hint"></p>
    </div>

    <!-- Footer -->
    <div class="login-footer">
        <strong>e-Aduan v3</strong> &nbsp;&middot;&nbsp; Bahagian Pengurusan Fasiliti<br>
        &copy; <?php echo date('Y'); ?> Universiti Teknologi MARA
    </div>

</div>

<script src="<?php echo baseUrl(); ?>/assets/js/pwa-register.js"></script>
<script src="<?php echo baseUrl(); ?>/assets/js/pwa-install.js"></script>
<script>
/*  Console Marker  */
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
