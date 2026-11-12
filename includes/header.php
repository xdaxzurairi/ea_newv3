<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle ?? 'e-Aduan'); ?> – Sistem Aduan UiTM</title>

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous" rel="stylesheet">
    <link href="<?php echo baseUrl(); ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<?php if (!empty($_SESSION['sso_authenticated'])): ?>
<nav class="navbar navbar-expand-lg ea-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?php echo baseUrl(); ?>/pages/borang.php">
            <img src="<?php echo baseUrl(); ?>/assets/images/logo-uitm.png" alt="UiTM">
            e-Aduan
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="<?php echo baseUrl(); ?>/pages/borang.php"><i class="bi bi-pencil-square"></i> Lapor Aduan</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo baseUrl(); ?>/pages/semak.php"><i class="bi bi-search"></i> Semak Aduan</a></li>
                <li class="nav-item">
                    <span class="nav-link text-white-50"><i class="bi bi-person-circle"></i> <?php echo e(sess('user_name', '')); ?></span>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?php echo baseUrl(); ?>/auth/logout.php"><i class="bi bi-box-arrow-right"></i> Keluar</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="ea-gold-strip"></div>
<?php endif; ?>

<main class="ea-main">
<div class="ea-container">
