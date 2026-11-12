<?php
declare(strict_types=1);

/**
 * Muat nilai dari fail .env ke $_ENV / getenv()
 * Dipanggil oleh config/database.php dan config/auth.php
 *
 * Utamakan .env.php: baris pertamanya `<?php exit; ?>` supaya jika diminta terus
 * melalui web, PHP melaksanakannya dan tiada kandungan dipulangkan. Pelayan Apache
 * ini mengabaikan .htaccess/web.config, jadi fail .env biasa boleh dimuat turun.
 * .env kekal sebagai fallback untuk pelayan yang belum dimigrasi.
 */
(function (): void {
    $path = null;
    foreach ([__DIR__ . '/../.env.php', __DIR__ . '/../.env'] as $candidate) {
        if (file_exists($candidate)) {
            $path = $candidate;
            break;
        }
    }
    if ($path === null) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name  = trim($name);
        $value = trim($value);
        // Buang quote luar jika ada
        if (strlen($value) >= 2 && (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        )) {
            $value = substr($value, 1, -1);
        }
        if ($name !== '' && !array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("$name=$value");
        }
    }
})();
