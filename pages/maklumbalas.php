<?php
/**
 * eAduan v3 – Maklumbalas Pelanggan
 * Popup – pengguna memberi maklumbalas selepas aduan siap (COM).
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    echo '<script>alert("Sesi tamat. Sila log masuk semula.");window.close();</script>';
    exit;
}

$wr_id = (int) req('id', '0');

// Hanya pemilik aduan boleh beri maklumbalas
$wr = fetchOwnWr($wr_id, 'wr_id, status, work_performance');
if (!$wr) {
    echo '<script>alert("Aduan tidak dijumpai.");window.close();</script>';
    exit;
}

$pdo = getPdo();
$saved = false;
$error = '';

// ---- Process POST ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && post('hantar') === '1') {
    validateCsrf();

    $work_performance   = post('work_performance', '');
    $satisfaction_notes = post('satisfaction_notes', '');

    if (!in_array($work_performance, ['1', '2'], true)) {
        $error = 'Sila pilih tahap kepuasan.';
    } else {
        try {
            [$ownerCol, $ownerVal] = currentOwner();
            // Atomic: hanya aduan SIAP (COM / status kosong, sama logik semak.php) yang belum dinilai.
            // Elak maklumbalas ditimpa & e-mel Pengarah dihantar berulang.
            $affected = dbExecute(
                "UPDATE wr SET satisfaction_notes=?, work_performance=?, satisfaction_date=GETDATE()
                 WHERE wr_id=? AND {$ownerCol}=?
                   AND UPPER(LTRIM(RTRIM(ISNULL(status,'')))) IN ('COM','')
                   AND (work_performance IS NULL OR LTRIM(RTRIM(work_performance)) = '')",
                [$satisfaction_notes, $work_performance, $wr_id, $ownerVal]
            );
            if ($affected > 0) {
                $saved = true;
                if ($work_performance === '2') {
                    require_once __DIR__ . '/../lib/email_pengarah.php';
                    email_pengarah($wr_id, $pdo);
                }
            } else {
                $error = 'Maklumbalas tidak dapat disimpan: aduan belum siap atau telah dinilai.';
            }
        } catch (Exception $ex) {
            error_log('maklumbalas gagal wr_id=' . $wr_id . ': ' . $ex->getMessage());
            $error = 'Ralat menyimpan maklumbalas. Sila cuba sebentar lagi.';
        }
    }
    $wr = fetchOwnWr($wr_id, 'wr_id, status, work_performance');
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maklumbalas – e-Aduan v3</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous" rel="stylesheet">
    <style>
        :root {
            --uitm-purple: #5B2D8E;
            --uitm-purple-dark: #3D1A6E;
            --uitm-gold: #D4A843;
            --uitm-gold-light: #F5E6C0;
        }
        body { font-family: 'Inter', sans-serif; font-size: 0.9rem; background: #f5f3f7; padding: 20px; }
        .ea-sec-hdr { background: linear-gradient(135deg, var(--uitm-purple), var(--uitm-purple-dark)); color: #fff; font-weight: 700; font-size: 0.88rem; padding: 10px 20px; border-radius: 10px 10px 0 0; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .btn-uitm { background: linear-gradient(135deg, var(--uitm-purple), var(--uitm-purple-dark)); color: #fff; border: none; border-radius: 8px; font-weight: 600; padding: 9px 22px; }
        .btn-uitm:hover { background: var(--uitm-purple-dark); color: #fff; }
        .form-check-input:checked { background-color: var(--uitm-purple); border-color: var(--uitm-purple); }
    </style>
</head>
<body>

<?php if ($saved): ?>
<div class="card">
    <div class="ea-sec-hdr"><i class="bi bi-check-circle"></i> Maklumbalas Dihantar</div>
    <div class="card-body text-center p-4">
        <i class="bi bi-patch-check-fill" style="font-size:2.5rem;color:var(--uitm-purple);"></i>
        <p class="mt-2 mb-3">Terima kasih atas maklumbalas anda untuk aduan <strong>#<?php echo e((string)$wr_id); ?></strong>.</p>
        <button type="button" class="btn btn-uitm" onclick="window.opener.location.reload();window.close();">
            <i class="bi bi-x-circle"></i> Tutup
        </button>
    </div>
</div>

<?php elseif ($wr && !empty($wr['work_performance'])): ?>
<div class="card">
    <div class="ea-sec-hdr"><i class="bi bi-chat-dots"></i> Maklumbalas</div>
    <div class="card-body text-center p-4">
        <p>Anda telah memberi maklumbalas untuk aduan <strong>#<?php echo e((string)$wr_id); ?></strong>.</p>
        <p>
            <?php echo $wr['work_performance'] == '1'
                ? '<i class="bi bi-hand-thumbs-up text-success fs-3"></i><br>Memuaskan'
                : '<i class="bi bi-hand-thumbs-down text-danger fs-3"></i><br>Tidak Memuaskan'; ?>
        </p>
        <button type="button" class="btn btn-uitm" onclick="window.close();">
            <i class="bi bi-x-circle"></i> Tutup
        </button>
    </div>
</div>

<?php else: ?>
<div class="card">
    <div class="ea-sec-hdr"><i class="bi bi-chat-dots"></i> Maklumbalas Pelanggan – Aduan #<?php echo e((string)$wr_id); ?></div>
    <div class="card-body p-4">

        <?php if ($error): ?>
        <div class="alert alert-danger" style="font-size:0.85rem;"><?php echo e($error); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <input type="hidden" name="id" value="<?php echo e((string)$wr_id); ?>">
            <input type="hidden" name="hantar" value="1">
            <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">

            <div class="mb-3">
                <label class="form-label fw-bold" style="color:var(--uitm-purple-dark);">Tahap Kepuasan <span class="text-danger">*</span></label>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="work_performance" id="wp1" value="1" required>
                    <label class="form-check-label" for="wp1">
                        <i class="bi bi-hand-thumbs-up text-success"></i> Memuaskan
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="work_performance" id="wp2" value="2" required>
                    <label class="form-check-label" for="wp2">
                        <i class="bi bi-hand-thumbs-down text-danger"></i> Tidak Memuaskan
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold" style="color:var(--uitm-purple-dark);">Catatan / Komen</label>
                <textarea name="satisfaction_notes" class="form-control" rows="4" placeholder="Tuliskan komen anda di sini (pilihan)..."></textarea>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-uitm flex-fill">
                    <i class="bi bi-send"></i> Hantar Maklumbalas
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.close();">
                    <i class="bi bi-x"></i> Batal
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
