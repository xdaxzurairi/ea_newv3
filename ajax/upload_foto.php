<?php
/**
 * eAduan v3 – Upload Foto Kerosakan (AJAX)
 * Terima gambar, compress < 1MB, simpan sementara dalam uploads/temp/
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (empty($_SESSION['sso_authenticated'])) {
    echo json_encode(['success' => false, 'error' => 'Sesi tamat. Sila log masuk semula.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['foto'])) {
    echo json_encode(['success' => false, 'error' => 'Tiada fail diterima.']);
    exit;
}

// CSRF (respons JSON — validateCsrf() pulang teks biasa)
if (empty($_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) ($_POST['csrf_token'] ?? ''))) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Permintaan tidak sah. Sila refresh halaman dan cuba semula.']);
    exit;
}

$file = $_FILES['foto'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Ralat semasa muat naik fail.']);
    exit;
}

// Tolak fail > 10MB (sebelum compress)
if ($file['size'] > 10 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'Saiz fail melebihi 10MB.']);
    exit;
}

// Validate MIME type
$finfo   = new finfo(FILEINFO_MIME_TYPE);
$mime    = $finfo->file($file['tmp_name']);
if ($mime === 'application/pdf') {
    echo json_encode(['success' => false, 'error' => 'Fail PDF tidak dibenarkan. Sila muat naik gambar (JPG/PNG) sahaja.']);
    exit;
}
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mime, $allowed, true)) {
    echo json_encode(['success' => false, 'error' => 'Jenis fail tidak dibenarkan. Sila muat naik gambar sahaja (JPG, PNG, GIF, WEBP).']);
    exit;
}

if (!extension_loaded('gd')) {
    echo json_encode(['success' => false, 'error' => 'GD library tidak tersedia pada pelayan.']);
    exit;
}

// Compress gambar ke < 1MB
$compressed = compressToUnder1MB($file['tmp_name'], $mime);
if ($compressed === false) {
    echo json_encode(['success' => false, 'error' => 'Gagal memproses gambar.']);
    exit;
}

$token = uploadToken();
$index = (int) ($_POST['index'] ?? 0);
if ($index < 0 || $index > 2) {
    echo json_encode(['success' => false, 'error' => 'Index gambar tidak sah.']);
    exit;
}
$filename = 'tmp_' . $token . '_' . $index . '_' . time() . '.jpg';
$dest     = __DIR__ . '/../uploads/temp/' . $filename;

if (file_put_contents($dest, $compressed) === false) {
    echo json_encode(['success' => false, 'error' => 'Gagal menyimpan fail sementara.']);
    exit;
}

$sizeKB = round(strlen($compressed) / 1024);
echo json_encode(['success' => true, 'filename' => $filename, 'size_kb' => $sizeKB]);

// ---- Helper: compress gambar supaya < 1MB ----
function compressToUnder1MB(string $srcPath, string $mime): string|false
{
    $img = imagecreatefromstring(file_get_contents($srcPath));
    if (!$img) return false;

    $origW = imagesx($img);
    $origH = imagesy($img);

    // Scale down jika dimensi > 1920px
    $maxDim = 1920;
    if ($origW > $maxDim || $origH > $maxDim) {
        $ratio = min($maxDim / $origW, $maxDim / $origH);
        $newW  = (int)round($origW * $ratio);
        $newH  = (int)round($origH * $ratio);
        $scaled = imagecreatetruecolor($newW, $newH);
        imagefill($scaled, 0, 0, imagecolorallocate($scaled, 255, 255, 255));
        imagecopyresampled($scaled, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($img);
        $img = $scaled;
    }

    $maxBytes = 1024 * 1024; // 1MB

    // Cuba kualiti dari 85 ke bawah sehingga < 1MB
    for ($quality = 85; $quality >= 30; $quality -= 10) {
        ob_start();
        imagejpeg($img, null, $quality);
        $data = ob_get_clean();
        if (strlen($data) <= $maxBytes) {
            imagedestroy($img);
            return $data;
        }
    }

    // Terakhir: kecilkan dimensi lagi jika masih besar
    $w     = imagesx($img);
    $h     = imagesy($img);
    $scale = sqrt($maxBytes / strlen($data)) * 0.85;
    $newW  = max(320, (int)round($w * $scale));
    $newH  = max(240, (int)round($h * $scale));
    $final = imagecreatetruecolor($newW, $newH);
    imagefill($final, 0, 0, imagecolorallocate($final, 255, 255, 255));
    imagecopyresampled($final, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
    imagedestroy($img);

    ob_start();
    imagejpeg($final, null, 70);
    $data = ob_get_clean();
    imagedestroy($final);

    return $data;
}
