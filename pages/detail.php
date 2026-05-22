<?php
/**
 * eAduan v3 – Butiran Aduan
 * Papar maklumat lengkap satu aduan.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

$wr_id = req('wrequested', '');
if (!$wr_id) {
    header('Location: ' . baseUrl() . '/pages/semak.php');
    exit;
}

$wr = dbFetchOne(
    "SELECT *,
            FORMAT(date_requested, 'dd-MM-yyyy') AS date_requested_fmt,
            FORMAT(time_requested, 'HH:mm:ss') AS time_requested_fmt,
            FORMAT(date_stat_chg, 'dd-MM-yyyy HH:mm:ss') AS date_stat_chg_fmt
     FROM wr WHERE wr_id = ?",
    [$wr_id]
);
if (!$wr) {
    $pageTitle = 'Aduan Tidak Dijumpai';
    require_once __DIR__ . '/../includes/header.php';
    echo '<div class="ea-card"><div class="card-body p-4 text-center">';
    echo '<i class="bi bi-exclamation-circle" style="font-size:2.5rem;color:#dc3545;"></i>';
    echo '<p class="mt-2">Aduan <strong>#' . e($wr_id) . '</strong> tidak dijumpai.</p>';
    echo '<a href="' . baseUrl() . '/pages/semak.php" class="btn btn-uitm mt-2"><i class="bi bi-arrow-left"></i> Kembali</a>';
    echo '</div></div>';
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// ---- Resolve names ----
$state_id  = trim($wr['state_id'] ?? '');
$site_id   = trim($wr['site_id'] ?? '');
$bl_id     = trim($wr['bl_id'] ?? '');
$fl_id     = trim($wr['fl_id'] ?? '');
$rm_id     = trim($wr['rm_id'] ?? '');
$section   = trim($wr['section'] ?? '');
$prob_group = trim($wr['prob_group'] ?? '');
$prob_type  = trim($wr['prob_type'] ?? '');
$infra_id   = trim($wr['infra_id'] ?? '');
$subinfra_id = trim($wr['subinfra_id'] ?? '');

$nama_negeri = $nama_kampus = $nama_bangunan = $nama_blok = $nama_aras = $nama_ruang = '-';
$nama_section = $nama_prob_group = $nama_prob_type = '-';
$nama_infra = $nama_subinfra = '-';

if ($state_id) {
    $r = dbFetchOne("SELECT name, state_id FROM state WHERE state_id = ?", [$state_id]);
    $nama_negeri = formatCodeDesc($state_id, $r ? trim($r['name']) : '');
}

// Resolve bldg_id from bl -> bldg
$bldg_id = '';
if ($bl_id) {
    $bl_r = dbFetchOne("SELECT site_id, state_id, bl_id, bldg_id, name, block_num FROM bl WHERE state_id = ? AND site_id = ? AND bl_id = ?", [$state_id, $site_id, $bl_id]);
    if (!$bl_r) {
        $bl_r = dbFetchOne("SELECT bldg_id, name FROM bl WHERE bl_id = ?", [$bl_id]);
    }
    if ($bl_r) {
        $nama_blok = formatCodeDesc($bl_id, trim($bl_r['name']));
        $bldg_id   = trim($bl_r['bldg_id'] ?? '');
    }
}

if ($state_id && $site_id) {
    $r = dbFetchOne("SELECT state_id, site_id, name FROM site WHERE state_id = ? AND site_id = ?", [$state_id, $site_id]);
    $nama_kampus = formatCodeDesc($site_id, $r ? trim($r['name']) : '');
}
if ($state_id && $site_id && $bldg_id) {
    $r = dbFetchOne("SELECT bl_name, state_id, site_id, bldg_id FROM bldg WHERE state_id = ? AND site_id = ? AND bldg_id = ?", [$state_id, $site_id, $bldg_id]);
    $nama_bangunan = formatCodeDesc($bldg_id, $r ? trim($r['bl_name']) : '');
}
if ($bl_id && $fl_id) {
    $r = dbFetchOne("SELECT fl_id, name FROM fl WHERE bl_id = ? AND fl_id = ?", [$bl_id, $fl_id]);
    $nama_aras = formatCodeDesc($fl_id, $r ? trim($r['name']) : '');
}
if ($rm_id && $bl_id && $fl_id) {
    $r = dbFetchOne("SELECT rm_id, bl_id, fl_id, name FROM rm WHERE rm_id = ? AND bl_id = ? AND fl_id = ?", [$rm_id, $bl_id, $fl_id]);
    $nama_ruang = formatCodeDesc($rm_id, $r ? trim($r['name']) : '');
}
if ($section) {
    $r = dbFetchOne("SELECT section, description FROM section WHERE section = ?", [$section]);
    $nama_section = formatCodeDesc($section, $r ? descSlashToDash($r['description']) : '');
}
if ($section && $prob_group) {
    $r = dbFetchOne("SELECT section, prob_group, description FROM prob_group WHERE section = ? AND prob_group = ?", [$section, $prob_group]);
    $nama_prob_group = formatCodeDesc($prob_group, $r ? descSlashToDash($r['description']) : '');
}
if ($section && $prob_group && $prob_type) {
    $r = dbFetchOne("SELECT section, prob_group, prob_type, description FROM probtype WHERE section = ? AND prob_group = ? AND prob_type = ?", [$section, $prob_group, $prob_type]);
    $nama_prob_type = formatCodeDesc($prob_type, $r ? descSlashToDash($r['description']) : '');
}
if ($infra_id) {
    $r = dbFetchOne("SELECT infra_id, infra_name FROM wr_kat_infra WHERE infra_id = ?", [$infra_id]);
    $nama_infra = formatCodeDesc($infra_id, $r ? trim($r['infra_name']) : '');
}
if ($subinfra_id) {
    $r = dbFetchOne("SELECT SUBINFRA_ID, SUBINFRA_NAME FROM WR_SUB_INFRA WHERE subinfra_id = ?", [$subinfra_id]);
    $nama_subinfra = formatCodeDesc($subinfra_id, $r ? trim($r['SUBINFRA_NAME']) : '');
}

// ---- Pelapor ----
$pelapor = [];
$em_number = trim($wr['em_number'] ?? '');
$stu_id_w  = trim($wr['stu_id'] ?? '');
$ic_no_w   = trim($wr['ic_no'] ?? '');

if ($em_number) {
    $row = dbFetchOne("SELECT em_number, em_id, phone, hp_number, email FROM em WHERE em_number = ?", [$em_number]);
    if ($row) {
        $pelapor = [
            'Nama'       => trim($row['em_id']),
            'No. Staf'   => trim($row['em_number']),
            'Telefon'    => trim($row['phone']),
            'H/P'        => trim($row['hp_number']),
            'E-mel'      => trim($row['email']),
        ];
    }
} elseif ($stu_id_w) {
    $row = dbFetchOne("SELECT stu_id, stu_name, stu_phone, email FROM stu WHERE stu_id = ?", [$stu_id_w]);
    if ($row) {
        $pelapor = [
            'Nama'        => trim($row['stu_name']),
            'No. Pelajar' => trim($row['stu_id']),
            'Telefon'     => trim($row['stu_phone']),
            'E-mel'       => trim($row['email']),
        ];
    }
} elseif ($ic_no_w) {
    $row = dbFetchOne("SELECT name_user, ic_no, tel_no, email FROM afm_user_osd WHERE ic_no = ?", [$ic_no_w]);
    if ($row) {
        $pelapor = [
            'Nama'     => trim($row['name_user']),
            'No. K/P'  => trim($row['ic_no']),
            'Telefon'  => trim($row['tel_no']),
            'E-mel'    => trim($row['email']),
        ];
    }
}

// ---- Status ----
$st = strtoupper(trim($wr['status'] ?? ''));
$statusInfo = getWrStatusInfo($st);
$status_label = $statusInfo['label'];
$status_badge = $statusInfo['badge'];

$mediumLabels = getMediumLabels();

$type_req_labels = [
    '1' => 'Staf UiTM',
    '2' => 'Pelajar',
    '3' => 'Pengguna Luar',
    '5' => 'Inisiatif (Penyelia)',
];
$type_req_val   = trim($wr['type_req'] ?? '');
$type_req_label = $type_req_labels[$type_req_val] ?? 'Staf UiTM';

$pageTitle = 'Butiran Aduan #' . $wr_id;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-3">
    <a href="<?php echo baseUrl(); ?>/pages/semak.php" class="btn btn-uitm-gold btn-sm">
        <i class="bi bi-arrow-left"></i> Kembali ke Senarai
    </a>
</div>

<div class="ea-card">
    <div class="ea-sec-hdr">
        <i class="bi bi-file-earmark-text"></i> Butiran Aduan #<?php echo e((string)$wr_id); ?>
        <span class="float-end badge <?php echo e($status_badge); ?>"><?php echo e($status_label); ?></span>
    </div>
    <div class="card-body p-3">

        <?php
        $foto_indexes = [];
        for ($fi = 1; $fi <= 3; $fi++) {
            if (!empty($wr['foto' . $fi])) {
                $foto_indexes[] = $fi;
            }
        }
        if (!empty($foto_indexes)): ?>
        <div class="ea-sec-hdr mb-2"><i class="bi bi-camera"></i> Gambar Kerosakan</div>
        <div class="row g-2 mb-3">
            <?php foreach ($foto_indexes as $i => $idx): ?>
            <div class="col-4">
                <img src="<?php echo baseUrl(); ?>/ajax/foto.php?wr_id=<?php echo e((string)$wr_id); ?>&idx=<?php echo $idx; ?>"
                     class="img-fluid rounded border" style="height:120px;width:100%;object-fit:cover;"
                     alt="Gambar <?php echo $i + 1; ?>">
                <div class="text-center mt-1" style="font-size:0.78rem;color:#666;">Gambar <?php echo $i + 1; ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-person"></i> Maklumat Pelapor</div>
        <table class="table table-borderless ea-table mb-3" style="font-size:0.88rem;">
        <?php
        $keys = array_keys($pelapor);
        for ($i = 0; $i < count($keys); $i += 2) {
            echo '<tr>';
            echo '<td class="lbl">' . e($keys[$i]) . '</td>';
            echo '<td>' . (e($pelapor[$keys[$i]]) ?: '<span class="text-muted">-</span>') . '</td>';
            if (isset($keys[$i + 1])) {
                echo '<td class="lbl">' . e($keys[$i + 1]) . '</td>';
                echo '<td>' . (e($pelapor[$keys[$i + 1]]) ?: '<span class="text-muted">-</span>') . '</td>';
            } else {
                echo '<td colspan="2"></td>';
            }
            echo '</tr>';
        }
        ?>
        <tr>
            <td class="lbl">Kategori Pengadu</td>
            <td colspan="3"><?php echo e($type_req_label); ?></td>
        </tr>
        </table>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-geo-alt"></i> Lokasi</div>        
        <table class="table table-borderless ea-table mb-3" style="font-size:0.88rem;">
            <tr><td class="lbl">Negeri</td><td><?php echo e($nama_negeri); ?></td></tr>
            <tr><td class="lbl">Kampus</td><td><?php echo e($nama_kampus); ?></td></tr>
            <tr><td class="lbl">Bangunan</td><td><?php echo e($nama_bangunan); ?></td></tr>
            <tr><td class="lbl">Blok</td><td><?php echo e($nama_blok); ?></td></tr>
            <?php if ($infra_id): ?>
            <tr><td class="lbl">Kategori Infra</td><td><?php echo e($nama_infra); ?></td></tr>
            <tr><td class="lbl">Sub Kategori Infra</td><td><?php echo e($nama_subinfra); ?></td></tr>
            <?php else: ?>
            <tr><td class="lbl">Aras</td><td><?php echo e($nama_aras); ?></td></tr>
            <tr><td class="lbl">Ruang</td><td><?php echo e($nama_ruang); ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($wr['koordinat_lokasi'])): ?>
            <tr><td class="lbl">Koordinat</td><td><?php echo e(trim($wr['koordinat_lokasi'])); ?></td></tr>
            <?php endif; ?>
        </table>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-tools"></i> Jenis Kerosakan</div>
        <table class="table table-borderless ea-table mb-3" style="font-size:0.88rem;">
            <tr><td class="lbl">Seksyen</td><td><?php echo e($nama_section); ?></td></tr>
            <tr><td class="lbl">Elemen</td><td><?php echo e($nama_prob_group); ?></td></tr>
            <tr><td class="lbl">Masalah</td><td><?php echo e($nama_prob_type); ?></td></tr>
            <tr><td class="lbl">Keterangan</td><td><?php echo e(trim($wr['description'] ?? '')); ?></td></tr>
            <tr><td class="lbl">Medium</td><td><?php echo e($mediumLabels[trim($wr['type_wr'] ?? '')] ?? trim($wr['type_wr'] ?? '')); ?></td></tr>
        </table>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-clock-history"></i> Status & Tarikh</div>
        <table class="table table-borderless ea-table mb-0" style="font-size:0.88rem;">
            <tr><td class="lbl">Status</td><td><span class="badge <?php echo e($status_badge); ?>"><?php echo e($status_label); ?></span></td></tr>
            <tr><td class="lbl">Tarikh Aduan</td><td><?php
                $tarikh_aduan = trim($wr['date_requested_fmt'] ?? '');
                $masa_aduan   = trim($wr['time_requested_fmt'] ?? '');
                echo e($tarikh_aduan . ($masa_aduan ? ' ' . $masa_aduan : ''));
            ?></td></tr>
            <?php if (!empty($wr['date_stat_chg_fmt'])): ?>
            <tr><td class="lbl">Tarikh Tugasan</td><td><?php echo e(trim($wr['date_stat_chg_fmt'])); ?></td></tr>
            <?php endif; ?>
            <?php if (!empty($wr['work_performance'])): ?>
            <tr><td class="lbl">Maklumbalas</td><td>
                <?php echo $wr['work_performance'] == '1' ? '<i class="bi bi-hand-thumbs-up text-success"></i> Memuaskan' : '<i class="bi bi-hand-thumbs-down text-danger"></i> Tidak Memuaskan'; ?>
            </td></tr>
            <?php endif; ?>
            <?php if (!empty($wr['satisfaction_notes'])): ?>
            <tr><td class="lbl">Catatan Maklumbalas</td><td><?php echo e(trim($wr['satisfaction_notes'])); ?></td></tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
