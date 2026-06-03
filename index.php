<?php
/**
 * eAduan v3  Landing / Login Page
 * Revamp: Corporate Split-Screen + Particle Network
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
    <title>e-Aduan v3 — Sistem Aduan UiTM</title>

    <meta name="theme-color" content="#5B2D8E">
    <meta name="description" content="Sistem pengurusan aduan fasiliti Universiti Teknologi MARA">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="e-Aduan">
    <meta name="application-name" content="e-Aduan v3">
    <meta name="msapplication-TileColor" content="#5B2D8E">
    <meta name="msapplication-TileImage" content="<?php echo baseUrl(); ?>/assets/icons/icon-144x144.png">
    <link rel="manifest" href="<?php echo baseUrl(); ?>/manifest.json">
    <link rel="icon" type="image/png" sizes="192x192" href="<?php echo baseUrl(); ?>/assets/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo baseUrl(); ?>/assets/icons/icon-192x192.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    :root {
        --purple:    #5B2D8E;
        --purple-dk: #3D1A6E;
        --purple-lt: #7B4FAE;
        --gold:      #D4A843;
        --gold-lt:   #FBF0D5;
        --text:      #1E1532;
        --muted:     #8B8499;
        --border:    #E8E3F0;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Inter', -apple-system, sans-serif; color: var(--text); }
    body { display: flex; min-height: 100vh; background: #fff; }

    /* ══ SPLIT LAYOUT ══ */
    .login-split {
        display: grid;
        grid-template-columns: 46% 54%;
        width: 100%;
        min-height: 100vh;
    }

    /* ══ BRAND PANEL ══ */
    .brand-panel {
        background: linear-gradient(150deg, #5B2D8E 0%, #3D1A6E 50%, #1E0A40 100%);
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 64px 48px;
        overflow: hidden;
    }

    /* Canvas fills the panel */
    #particle-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
    }

    /* Vignette so edges are deeper */
    .brand-panel::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(ellipse at center, transparent 40%, rgba(20,5,50,0.55) 100%);
        pointer-events: none;
        z-index: 1;
    }

    .brand-content {
        position: relative;
        z-index: 2;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .brand-logo {
        height: 96px;
        width: auto;
        object-fit: contain;
        margin-bottom: 32px;
        filter: drop-shadow(0 4px 16px rgba(0,0,0,0.4));
        animation: logo-float 6s ease-in-out infinite;
    }
    @keyframes logo-float {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-6px); }
    }

    .brand-gold-bar {
        width: 44px;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--gold), transparent);
        border-radius: 2px;
        margin: 0 auto 20px;
        animation: bar-glow 3s ease-in-out infinite;
    }
    @keyframes bar-glow {
        0%, 100% { opacity: 0.7; width: 44px; }
        50%       { opacity: 1;   width: 64px; }
    }

    .brand-title {
        font-size: 3rem;
        font-weight: 800;
        color: #fff;
        letter-spacing: -0.045em;
        line-height: 1.05;
        margin-bottom: 12px;
        text-shadow: 0 2px 24px rgba(91,45,142,0.5);
    }

    .brand-subtitle {
        font-size: 0.82rem;
        color: rgba(255,255,255,0.55);
        font-weight: 400;
        line-height: 1.65;
        max-width: 230px;
        margin: 0 auto 36px;
    }

    .brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.07);
        border: 1px solid rgba(255,255,255,0.13);
        border-radius: 100px;
        padding: 9px 20px;
        color: rgba(255,255,255,0.72);
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    .brand-badge i { color: var(--gold); }

    .brand-version {
        position: absolute;
        bottom: 24px;
        left: 0; right: 0;
        text-align: center;
        font-size: 0.62rem;
        color: rgba(255,255,255,0.18);
        font-weight: 500;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        z-index: 2;
    }

    /* ══ FORM PANEL ══ */
    .form-panel {
        background: #FFFFFF;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 64px 60px;
        position: relative;
    }
    .form-panel::before {
        content: '';
        position: absolute;
        left: 0; top: 12%; bottom: 12%;
        width: 1px;
        background: linear-gradient(to bottom, transparent, var(--border) 35%, var(--border) 65%, transparent);
    }

    .form-container {
        width: 100%;
        max-width: 360px;
    }

    /* Staggered entrance */
    .form-heading,
    .alert-wrap,
    .btn-google,
    .login-divider,
    .btn-luar,
    .pwa-install-section,
    .form-footer {
        opacity: 0;
        animation: elem-rise 0.55s cubic-bezier(0.16,1,0.3,1) both;
    }
    @keyframes elem-rise {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .form-heading         { animation-delay: 0.10s; }
    .alert-wrap           { animation-delay: 0.18s; }
    .btn-google           { animation-delay: 0.20s; }
    .login-divider        { animation-delay: 0.27s; }
    .btn-luar             { animation-delay: 0.32s; }
    .pwa-install-section  { animation-delay: 0.38s; }
    .form-footer          { animation-delay: 0.46s; }

    .form-heading { margin-bottom: 32px; }

    .form-heading-eyebrow {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        color: var(--purple-lt);
        margin-bottom: 8px;
    }

    .form-heading-title {
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -0.04em;
        line-height: 1.1;
        margin-bottom: 6px;
        background: linear-gradient(135deg, var(--purple-dk) 0%, var(--purple-lt) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .form-heading-sub {
        font-size: 0.84rem;
        color: var(--muted);
        font-weight: 400;
    }

    /* ── Alerts ── */
    .login-alert {
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 0.8rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid;
    }
    .login-alert-success { background: #F0FDF6; border-color: #A7F3C3; color: #166534; }
    .login-alert-warning { background: var(--gold-lt); border-color: rgba(212,168,67,0.4); color: #92400e; }

    /* ── Buttons ── */
    .login-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 13px 20px;
        border-radius: 6px;
        font-family: 'Inter', sans-serif;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        transition: box-shadow 0.18s ease, background 0.18s ease, border-color 0.18s ease;
        border: none;
        letter-spacing: -0.01em;
    }
    .login-btn + .login-btn { margin-top: 10px; }
    .login-btn i { font-size: 1rem; }

    .btn-google {
        background: #fff;
        color: #3C3C3C;
        border: 1.5px solid var(--border);
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    }
    .btn-google:hover {
        border-color: #4285F4;
        box-shadow: 0 3px 14px rgba(66,133,244,0.18);
        color: #1a1a1a;
        background: #FAFCFF;
    }
    .btn-google svg { flex-shrink: 0; }

    .btn-luar {
        background: var(--gold-lt);
        color: #7A5C10;
        border: 1.5px solid rgba(212,168,67,0.38);
    }
    .btn-luar:hover {
        background: var(--gold);
        color: #fff;
        border-color: var(--gold);
        box-shadow: 0 3px 14px rgba(212,168,67,0.28);
    }

    /* ── Divider ── */
    .login-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 14px 0;
    }
    .login-divider::before, .login-divider::after {
        content: ''; flex: 1; height: 1px; background: var(--border);
    }
    .login-divider span {
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #C8C0D4;
    }

    /* ── PWA ── */
    .pwa-install-section { margin-top: 6px; }
    .pwa-install-divider {
        display: flex; align-items: center; gap: 12px; margin-bottom: 12px;
    }
    .pwa-install-divider::before, .pwa-install-divider::after {
        content: ''; flex: 1; height: 1px; background: var(--border);
    }
    .pwa-install-divider span {
        font-size: 0.63rem;
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
        background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.18) 50%, transparent 100%);
        transform: translateX(-100%);
        animation: shimmer 2.5s infinite;
    }
    @keyframes shimmer { to { transform: translateX(100%); } }
    .btn-pwa-install:hover { box-shadow: 0 3px 14px rgba(25,135,84,0.28); color: #fff; }
    .pwa-install-hint {
        text-align: center;
        font-size: 0.68rem;
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
        border-radius: 6px;
        background: #F0FDF6;
        border: 1.5px solid #A7F3C3;
        color: #166534;
        font-size: 0.82rem;
        font-weight: 600;
    }

    /* ── iOS PWA Modal ── */
    .pwa-ios-overlay {
        position: fixed; inset: 0; z-index: 10000;
        background: rgba(0,0,0,0.45);
        display: flex; align-items: flex-end; justify-content: center;
        padding: 16px;
        animation: fade-in 0.2s;
    }
    @keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
    .pwa-ios-modal {
        background: #fff; border-radius: 16px; padding: 28px 24px;
        max-width: 380px; width: 100%; text-align: center;
        animation: slide-up 0.3s cubic-bezier(0.4,0,0.2,1);
    }
    @keyframes slide-up {
        from { transform: translateY(40px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
    .pwa-ios-modal h3 { font-size: 0.95rem; font-weight: 700; color: var(--purple-dk); margin-bottom: 16px; }
    .pwa-ios-step {
        display: flex; align-items: center; gap: 12px;
        text-align: left; padding: 10px 0;
        border-bottom: 1px solid #F0F0F0; font-size: 0.82rem; color: #333;
    }
    .pwa-ios-step:last-child { border-bottom: none; }
    .pwa-ios-step-num {
        width: 26px; height: 26px; border-radius: 50%;
        background: var(--purple); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.72rem; font-weight: 700; flex-shrink: 0;
    }
    .pwa-ios-close {
        margin-top: 16px; background: none;
        border: 1.5px solid #DDD; border-radius: 6px;
        padding: 10px 24px; font-family: inherit;
        font-size: 0.82rem; font-weight: 600; color: #666; cursor: pointer;
    }
    .pwa-ios-close:hover { background: #F5F5F5; }

    /* ── Footer ── */
    .form-footer {
        text-align: center;
        margin-top: 28px;
        padding-top: 22px;
        border-top: 1px solid var(--border);
        font-size: 0.67rem;
        color: var(--muted);
        line-height: 1.85;
    }
    .form-footer strong { color: var(--purple-lt); font-weight: 600; }

    /* ══ RESPONSIVE ══ */
    @media (max-width: 900px) {
        .login-split { grid-template-columns: 1fr; grid-template-rows: auto 1fr; }
        .brand-panel {
            flex-direction: row; justify-content: flex-start;
            align-items: center; gap: 14px;
            padding: 18px 20px; min-height: unset;
        }
        #particle-canvas { display: none; }
        .brand-panel::after { display: none; }
        .brand-gold-bar, .brand-badge, .brand-subtitle, .brand-version { display: none; }
        .brand-content { flex-direction: row; text-align: left; gap: 14px; align-items: center; }
        .brand-logo { height: 42px; margin-bottom: 0; animation: none; }
        .brand-title { font-size: 1.3rem; margin-bottom: 0; }
        .form-panel { padding: 36px 24px; justify-content: flex-start; padding-top: 40px; }
        .form-panel::before { display: none; }
        .form-container { max-width: 440px; margin: 0 auto; }
    }
    @media (max-width: 480px) {
        .brand-panel { padding: 16px 18px; }
        .brand-title  { font-size: 1.15rem; }
        .form-panel   { padding: 28px 18px; }
    }
    </style>
</head>
<body>

<div class="login-split">

    <!-- ══ LEFT: BRAND PANEL ══ -->
    <div class="brand-panel" id="brand-panel">
        <canvas id="particle-canvas"></canvas>

        <div class="brand-content">
            <img src="<?php echo baseUrl(); ?>/assets/images/logo-uitm.png" alt="Logo UiTM" class="brand-logo">
            <div class="brand-gold-bar"></div>
            <h1 class="brand-title">e-Aduan v3</h1>
            <p class="brand-subtitle">Sistem Pengurusan Aduan Fasiliti<br>Universiti Teknologi MARA</p>
            <div class="brand-badge">
                <i class="bi bi-shield-check-fill"></i>
                Portal Rasmi UiTM
            </div>
        </div>

        <div class="brand-version">ea_newv3 &nbsp;&middot;&nbsp; XDIBAX Innovation</div>
    </div>

    <!-- ══ RIGHT: FORM PANEL ══ -->
    <div class="form-panel">
        <div class="form-container">

            <div class="form-heading">
                <div class="form-heading-eyebrow">e-Aduan v3</div>
                <div class="form-heading-title">Selamat Datang</div>
                <div class="form-heading-sub">Sila log masuk untuk meneruskan</div>
            </div>

            <?php if (!empty($_GET['logout']) || !empty($_GET['expired'])): ?>
            <div class="alert-wrap">
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
            </div>
            <?php endif; ?>

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

            <div id="pwa-install-section" class="pwa-install-section">
                <div class="pwa-install-divider"><span>Pasang Aplikasi</span></div>
                <button id="pwa-install-main-btn" class="login-btn btn-pwa-install" type="button">
                    <i class="bi bi-download"></i> Pasang e-Aduan
                </button>
                <p class="pwa-install-hint" id="pwa-install-hint"></p>
            </div>

            <div class="form-footer">
                <strong>Bahagian Pengurusan Fasiliti</strong><br>
                &copy; <?php echo date('Y'); ?> Universiti Teknologi MARA. Hak cipta terpelihara.
            </div>

        </div>
    </div>

</div>

<script src="<?php echo baseUrl(); ?>/assets/js/pwa-register.js"></script>
<script src="<?php echo baseUrl(); ?>/assets/js/pwa-install.js"></script>

<!-- Particle Network Engine -->
<script>
(function () {
    'use strict';

    if (window.innerWidth <= 900) return;

    var canvas = document.getElementById('particle-canvas');
    var panel  = document.getElementById('brand-panel');
    if (!canvas || !panel) return;

    var ctx = canvas.getContext('2d');
    var W, H;
    var TOTAL     = 72;
    var LINK_DIST = 125;
    var REPEL_R   = 90;
    var particles = [];
    var mouse     = { x: -9999, y: -9999 };
    var raf;

    /* ── resize ── */
    function resize() {
        W = canvas.width  = panel.offsetWidth;
        H = canvas.height = panel.offsetHeight;
    }

    /* ── Particle ── */
    function Particle() {
        this.init();
    }
    Particle.prototype.init = function () {
        this.x  = Math.random() * W;
        this.y  = Math.random() * H;
        this.vx = (Math.random() - 0.5) * 0.55;
        this.vy = (Math.random() - 0.5) * 0.55;
        /* type: 0=white, 1=gold, 2=large-white */
        var r = Math.random();
        if (r < 0.10) {
            this.type = 2; this.r = 3.2 + Math.random() * 1.4;
        } else if (r < 0.25) {
            this.type = 1; this.r = 1.8 + Math.random() * 1.0;
        } else {
            this.type = 0; this.r = 1.0 + Math.random() * 1.2;
        }
        this.pulse = Math.random() * Math.PI * 2;
        this.pulseSpeed = 0.02 + Math.random() * 0.02;
    };

    Particle.prototype.update = function () {
        this.pulse += this.pulseSpeed;

        /* Mouse repel */
        var dx = this.x - mouse.x;
        var dy = this.y - mouse.y;
        var d2 = dx * dx + dy * dy;
        if (d2 < REPEL_R * REPEL_R && d2 > 0.01) {
            var d    = Math.sqrt(d2);
            var f    = ((REPEL_R - d) / REPEL_R) * 0.4;
            this.vx += (dx / d) * f;
            this.vy += (dy / d) * f;
        }

        /* Damp speed */
        var spd = Math.sqrt(this.vx * this.vx + this.vy * this.vy);
        if (spd > 1.4) { var k = 1.4 / spd; this.vx *= k; this.vy *= k; }

        this.x += this.vx;
        this.y += this.vy;

        /* Bounce */
        if (this.x < 0)  { this.x = 0;  this.vx *= -1; }
        if (this.x > W)  { this.x = W;  this.vx *= -1; }
        if (this.y < 0)  { this.y = 0;  this.vy *= -1; }
        if (this.y > H)  { this.y = H;  this.vy *= -1; }
    };

    Particle.prototype.draw = function () {
        var pulse = 0.3 + Math.sin(this.pulse) * 0.2;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        if (this.type === 1) {
            ctx.fillStyle = 'rgba(212,168,67,' + (0.7 + Math.sin(this.pulse) * 0.3) + ')';
        } else if (this.type === 2) {
            ctx.fillStyle = 'rgba(255,255,255,' + (0.5 + Math.sin(this.pulse) * 0.2) + ')';
        } else {
            ctx.fillStyle = 'rgba(255,255,255,' + pulse + ')';
        }
        ctx.fill();
    };

    /* ── Draw connection lines ── */
    function drawLinks() {
        for (var i = 0; i < particles.length - 1; i++) {
            var a = particles[i];
            for (var j = i + 1; j < particles.length; j++) {
                var b  = particles[j];
                var dx = a.x - b.x;
                var dy = a.y - b.y;
                var d  = Math.sqrt(dx * dx + dy * dy);
                if (d >= LINK_DIST) continue;
                var alpha = (1 - d / LINK_DIST) * 0.28;
                ctx.beginPath();
                ctx.moveTo(a.x, a.y);
                ctx.lineTo(b.x, b.y);
                ctx.strokeStyle = (a.type === 1 || b.type === 1)
                    ? 'rgba(212,168,67,' + alpha + ')'
                    : 'rgba(190,160,240,' + alpha + ')';
                ctx.lineWidth = 0.7;
                ctx.stroke();
            }
        }
    }

    /* ── Animate ── */
    function loop() {
        ctx.clearRect(0, 0, W, H);
        drawLinks();
        for (var i = 0; i < particles.length; i++) {
            particles[i].update();
            particles[i].draw();
        }
        raf = requestAnimationFrame(loop);
    }

    /* ── Mouse tracking on panel ── */
    panel.addEventListener('mousemove', function (e) {
        var rect = canvas.getBoundingClientRect();
        mouse.x = e.clientX - rect.left;
        mouse.y = e.clientY - rect.top;
    });
    panel.addEventListener('mouseleave', function () {
        mouse.x = -9999; mouse.y = -9999;
    });

    /* ── Init ── */
    resize();
    for (var i = 0; i < TOTAL; i++) particles.push(new Particle());
    loop();

    window.addEventListener('resize', function () {
        if (window.innerWidth <= 900) { cancelAnimationFrame(raf); return; }
        resize();
    });
})();
</script>

<script>
(function () {
    var s = 'background:linear-gradient(135deg,#5B2D8E,#3D1A6E);color:#FFF;padding:6px 14px;border-radius:4px;font-family:Inter,sans-serif;font-size:12px;font-weight:700;letter-spacing:0.08em;';
    var g = 'background:transparent;color:#D4A843;font-family:Inter,sans-serif;font-size:11px;font-weight:500;';
    console.log('%c e-Aduan v3 %c', s, '');
    console.log('%c Developer  : Mohammad Zurairi', g);
    console.log('%c System     : ea_newv3 | XDIBAX Innovation', g);
    console.log('%c Particles  : Active — ' + new Date().getFullYear(), g);
})();
</script>
</body>
</html>
