<?php
declare(strict_types=1);

/**
 * Migrasi gambar aduan lokal (uploads/wr/*) ke MinIO.
 * CLI sahaja:  php tools/migrate_foto_minio.php [--dry-run]
 *
 * Untuk setiap wr.foto1..3 yang masih nama fail lokal (tiada '/'):
 *   upload ke MinIO → UPDATE wr (hanya jika nilai belum berubah) → padam fail lokal.
 * Fail lokal tanpa rujukan DB hanya dilaporkan, tidak dipadam.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/minio.php';

$dryRun = in_array('--dry-run', $argv, true);
$dir    = __DIR__ . '/../uploads/wr/';

if (!$dryRun && getMinioClient() === null) {
    fwrite(STDERR, "MinIO tidak dikonfigurasi (.env.php: MINIO_*). Berhenti.\n");
    exit(1);
}

$rows = dbFetchAll(
    "SELECT wr_id, foto1, foto2, foto3 FROM wr
     WHERE (foto1 IS NOT NULL AND foto1 <> '' AND CHARINDEX('/', foto1) = 0)
        OR (foto2 IS NOT NULL AND foto2 <> '' AND CHARINDEX('/', foto2) = 0)
        OR (foto3 IS NOT NULL AND foto3 <> '' AND CHARINDEX('/', foto3) = 0)
     ORDER BY wr_id"
);

$ok = $skip = $fail = 0;
$referenced = [];

foreach ($rows as $r) {
    $wr_id = (int) $r['wr_id'];
    for ($i = 1; $i <= 3; $i++) {
        $old = trim((string) ($r["foto{$i}"] ?? ''));
        if ($old === '' || str_contains($old, '/')) {
            continue;
        }
        $file = basename($old);
        $referenced[$file] = true;
        $path = $dir . $file;

        if (!is_file($path)) {
            echo "[tiada fail] wr {$wr_id} foto{$i}: {$file}\n";
            $skip++;
            continue;
        }
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)) ?: 'jpg';

        if ($dryRun) {
            echo "[dry-run] wr {$wr_id} foto{$i}: {$file} → ea_newv3/{$wr_id}/foto{$i}.{$ext}\n";
            $ok++;
            continue;
        }

        $key = minioUpload($path, "{$wr_id}/foto{$i}.{$ext}");
        if ($key === null) {
            echo "[GAGAL upload] wr {$wr_id} foto{$i}: {$file}\n";
            $fail++;
            continue;
        }
        $n = dbExecute("UPDATE wr SET foto{$i} = ? WHERE wr_id = ? AND foto{$i} = ?", [$key, $wr_id, $r["foto{$i}"]]);
        if ($n !== 1) {
            echo "[DB berubah, fail lokal dikekalkan] wr {$wr_id} foto{$i}\n";
            $fail++;
            continue;
        }
        unlink($path);
        echo "[OK] wr {$wr_id} foto{$i}: {$file} → {$key}\n";
        $ok++;
    }
}

foreach (glob($dir . '*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE) ?: [] as $f) {
    if (!isset($referenced[basename($f)])) {
        echo "[tanpa rujukan DB — tidak dipadam] " . basename($f) . "\n";
    }
}

echo "\nSelesai" . ($dryRun ? ' (dry-run)' : '') . ": {$ok} berjaya, {$skip} tiada fail, {$fail} gagal.\n";
