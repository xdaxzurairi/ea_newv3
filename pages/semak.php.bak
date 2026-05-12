<?php
/**
 * eAduan v3 – Semakan Aduan
 * Papar senarai aduan pengguna dengan pagination.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

$requestor    = sess('requestor', '1');
$txt_emnumber = sess('txt_emnumber', '');
$no_stud      = sess('no_stud', '');
$no_kp        = sess('no_kp', '');

// Determine filter column and value
if ($requestor == '1' && $txt_emnumber) {
    $col = 'em_number';
    $val = $txt_emnumber;
} elseif ($requestor == '2' && $no_stud) {
    $col = 'stu_id';
    $val = $no_stud;
} elseif ($requestor == '3' && $no_kp) {
    $col = 'ic_no';
    $val = $no_kp;
} else {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

$perPage = 10;
$page    = max(1, (int) req('page', '1'));
$offset  = ($page - 1) * $perPage;

$dbError = null;

try {
    $countRow   = dbFetchOne("SELECT COUNT(*) AS total FROM wr WHERE {$col} = ?", [$val]);
    $totalRows  = $countRow ? (int) $countRow['total'] : 0;
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    if ($page > $totalPages) $page = $totalPages;

    $offsetInt = (int) $offset;
    $perPageInt = (int) $perPage;
    $sql = "SELECT wr_id, FORMAT(date_requested, 'dd-MM-yyyy') AS date_requested, 
                   FORMAT(time_requested, 'HH:mm:ss') AS time_requested, 
                   status, prob_group, prob_type, bl_id, type_wr, section, 
                   date_assigned, date_stat_chg, work_performance
            FROM wr WHERE {$col} = ? 
            ORDER BY wr_id DESC
            OFFSET " . $offsetInt . " ROWS FETCH NEXT " . $perPageInt . " ROWS ONLY";

    $rows = dbFetchAll($sql, [$val]);
} catch (Throwable $e) {
    $dbError   = $e->getMessage();
    $rows      = [];
    $totalRows = 0;
    $totalPages = 1;
}

$statusMap = [
    'R'   => ['label' => 'PERMOHONAN',                                           'badge' => 'badge-status-r'],
    'I'   => ['label' => 'ARAHAN KERJA DIKELUARKAN',                             'badge' => 'badge-status-w'],
    'APP' => ['label' => 'DILULUSKAN',                                           'badge' => 'badge-status-w'],
    'W'   => ['label' => 'DALAM TINDAKAN',                                       'badge' => 'badge-status-w'],
    'COM' => ['label' => 'SIAP',                                                 'badge' => 'badge-status-com'],
    'S'   => ['label' => 'SIAP',                                                 'badge' => 'badge-status-com'],
    'CAN' => ['label' => 'DIBATALKAN',                                            'badge' => 'badge-status-can'],
    'REJ' => ['label' => 'DITOLAK',                                               'badge' => 'badge-status-can'],
    'A'   => ['label' => 'DITOLAK',                                               'badge' => 'badge-status-can'],
    'CLS' => ['label' => 'DITUTUP',                                               'badge' => 'badge-status-com'],
    'CLO' => ['label' => 'DITUTUP',                                               'badge' => 'badge-status-com'],
    'PEN' => ['label' => 'PENDING',                                               'badge' => 'badge-status-r'],
    'HLD' => ['label' => 'DITANGGUH',                                             'badge' => 'badge-status-r'],
    'REV' => ['label' => 'DISEMAK SEMULA DAN OPERASI DIHENTIKAN SEMENTARA',        'badge' => 'badge-status-r'],
    'AA'  => ['label' => 'WORK ORDER DIKELUARKAN',                                'badge' => 'badge-status-w'],
    'HP'  => ['label' => 'OPERASI DIHENTIKAN SEMENTARA MENUNGGU PERALATAN',        'badge' => 'badge-status-r'],
    'HA'  => ['label' => 'OPERASI DIHENTIKAN SEMENTARA MENUNGGU KEBENARAN MASUK', 'badge' => 'badge-status-r'],
    'HL'  => ['label' => 'OPERASI DIHENTIKAN SEMENTARA MENUNGGU PEKERJA',         'badge' => 'badge-status-r'],
    'WC'  => ['label' => 'SALAH SALURAN',                                         'badge' => 'badge-status-can'],
    'DEF' => ['label' => 'KECACATAN DALAM TEMPOH TANGGUNGAN',                     'badge' => 'badge-status-r'],
];

// Medium = kolum type_wr dalam wr; satu sumber label dengan DB
$mediumLabels = getMediumLabels();

$pageTitle = 'Semak Aduan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ea-card">
    <div class="ea-sec-hdr"><i class="bi bi-list-check"></i> Senarai Aduan Anda</div>
    <div class="card-body p-3">
        <?php if ($dbError): ?>
        <div class="alert alert-danger" style="font-size:0.85rem;">
            <strong>Ralat pangkalan data.</strong><br>
            <code><?php echo e($dbError); ?></code>
        </div>
        <?php endif; ?>

        <p class="text-muted mb-3" style="font-size:0.85rem;">
            Jumlah aduan: <strong><?php echo $totalRows; ?></strong> &middot; Halaman <?php echo $page; ?> / <?php echo $totalPages; ?>
        </p>

        <?php if (empty($rows)): ?>
        <div class="text-center py-5">
            <i class="bi bi-inbox" style="font-size:2.5rem;color:var(--uitm-purple-light);"></i>
            <p class="text-muted mt-2">Tiada aduan dijumpai.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover ea-table table-semak align-middle mb-0">
                <thead>
                    <tr>
                        <th class="col-bil">Bil</th>
                        <th>No Aduan</th>
                        <th>Tarikh</th>
                        <th>Masa</th>
                        <th>Lokasi</th>
                        <th>Jenis Kerosakan</th>
                        <th>Medium</th>
                        <th class="col-status">Status</th>
                        <th class="col-maklumbalas">Maklumbalas</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $i => $row):
                    $st   = strtoupper(trim($row['status'] ?? ''));
                    if ($st === '') $st = 'COM';
                    $info = $statusMap[$st] ?? ['label' => $st, 'badge' => 'bg-secondary'];
                    $bil  = $offset + $i + 1;

                    // Lokasi name from bl_id
                    $lokasi = $row['bl_id'] ?? '-';
                    $bl_r = dbFetchOne("SELECT name FROM bl WHERE bl_id = ?", [trim($row['bl_id'] ?? '')]);
                    if ($bl_r) $lokasi = trim($bl_r['name']);

                    // Jenis kerosakan
                    $jenis = '-';
                    $pt = dbFetchOne(
                        "SELECT description FROM probtype WHERE section = ? AND prob_group = ? AND prob_type = ?",
                        [trim($row['section'] ?? ''), trim($row['prob_group'] ?? ''), trim($row['prob_type'] ?? '')]
                    );
                    if ($pt) $jenis = descSlashToDash($pt['description']);
                ?>
                <tr>
                    <td class="col-bil"><span class="ea-card-lbl d-md-none">Bil: </span><?php echo $bil; ?></td>
                    <td><span class="ea-card-lbl d-md-none">No Aduan: </span>
                        <a href="<?php echo baseUrl(); ?>/pages/detail.php?wrequested=<?php echo e((string)$row['wr_id']); ?>" class="fw-bold" style="color:var(--uitm-purple);">
                            <?php echo e((string)$row['wr_id']); ?>
                        </a>
                    </td>
                    <td><span class="ea-card-lbl d-md-none">Tarikh: </span><?php echo e($row['date_requested'] ?? '-'); ?></td>
                    <td><span class="ea-card-lbl d-md-none">Masa: </span><?php echo e($row['time_requested'] ?? '-'); ?></td>
                    <td><span class="ea-card-lbl d-md-none">Lokasi: </span><?php echo e($lokasi); ?></td>
                    <td><span class="ea-card-lbl d-md-none">Perkara Aduan: </span><?php echo e($jenis); ?></td>
                    <td><span class="ea-card-lbl d-md-none">Medium: </span><?php echo e($mediumLabels[$row['type_wr'] ?? ''] ?? $row['type_wr'] ?? '-'); ?></td>
                    <td class="col-status"><span class="ea-card-lbl d-md-none">Status: </span><span class="badge <?php echo $info['badge']; ?>"><?php echo e($info['label']); ?></span></td>
                    <td class="col-maklumbalas"><span class="ea-card-lbl d-md-none">Maklumbalas: </span>
                        <?php if ($st === 'COM' && empty($row['work_performance'])): ?>
                        <a href="javascript:void(0);" onclick="window.open('<?php echo baseUrl(); ?>/pages/maklumbalas.php?id=<?php echo e((string)$row['wr_id']); ?>','maklumbalas','width=600,height=500,scrollbars=yes');" class="btn btn-sm btn-uitm-gold">
                            <i class="bi bi-chat-dots"></i> Maklumbalas
                        </a>
                        <?php elseif (!empty($row['work_performance'])): ?>
                        <span class="text-muted" style="font-size:0.82rem;">
                            <?php echo $row['work_performance'] == '1' ? '<i class="bi bi-hand-thumbs-up text-success"></i> Memuaskan' : '<i class="bi bi-hand-thumbs-down text-danger"></i> Tidak Memuaskan'; ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <nav class="mt-3" aria-label="Pagination">
            <ul class="pagination pagination-sm justify-content-center mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">&laquo;</a>
                </li>
                <?php
                $start = max(1, $page - 3);
                $end   = min($totalPages, $page + 3);
                for ($p = $start; $p <= $end; $p++): ?>
                <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $p; ?>"><?php echo $p; ?></a>
                </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">&raquo;</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
