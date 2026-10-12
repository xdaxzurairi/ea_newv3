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

// Hanya pemilik aduan boleh lihat (elak IDOR — data pelapor adalah PII)
$wr = fetchOwnWr((int) $wr_id,
    "*,
     FORMAT(date_requested, 'dd-MM-yyyy') AS date_requested_fmt,
     FORMAT(time_requested, 'HH:mm:ss') AS time_requested_fmt,
     FORMAT(date_stat_chg, 'dd-MM-yyyy HH:mm:ss') AS date_stat_chg_fmt"
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
$bldg_id = '';

// Satu query untuk semua data lokasi (state, kampus, blok, bangunan, aras, ruang)
if ($bl_id) {
    $loc = dbFetchOne(
        "SELECT
            bl.name       AS bl_name,
            bl.bldg_id    AS bldg_id,
            st.name       AS state_name,
            si.name       AS site_name,
            bg.bl_name    AS bldg_name,
            fl.name       AS fl_name,
            rm.name       AS rm_name
         FROM bl
         LEFT JOIN state st ON st.state_id = ?
         LEFT JOIN site  si ON si.state_id = ? AND si.site_id = ?
         LEFT JOIN bldg  bg ON bg.state_id = ? AND bg.site_id = ? AND bg.bldg_id = bl.bldg_id
         LEFT JOIN fl       ON fl.bl_id = bl.bl_id AND fl.fl_id = ?
         LEFT JOIN rm       ON rm.bl_id = bl.bl_id AND rm.fl_id = ? AND rm.rm_id = ?
         WHERE bl.bl_id = ?",
        [$state_id, $state_id, $site_id, $state_id, $site_id, $fl_id, $fl_id, $rm_id, $bl_id]
    );
    if ($loc) {
        $bldg_id      = trim($loc['bldg_id'] ?? '');
        $nama_negeri  = formatCodeDesc($state_id,  trim($loc['state_name'] ?? ''));
        $nama_kampus  = formatCodeDesc($site_id,   trim($loc['site_name']  ?? ''));
        $nama_bangunan = formatCodeDesc($bldg_id,  trim($loc['bldg_name']  ?? ''));
        $nama_blok    = formatCodeDesc($bl_id,     trim($loc['bl_name']    ?? ''));
        $nama_aras    = $fl_id ? formatCodeDesc($fl_id, trim($loc['fl_name'] ?? '')) : '-';
        $nama_ruang   = $rm_id ? formatCodeDesc($rm_id, trim($loc['rm_name'] ?? '')) : '-';
    }
} elseif ($state_id) {
    $r = dbFetchOne("SELECT name FROM state WHERE state_id = ?", [$state_id]);
    $nama_negeri = formatCodeDesc($state_id, $r ? trim($r['name']) : '');
}

// Satu query untuk seksyen, elemen, dan masalah
if ($section) {
    $pt = dbFetchOne(
        "SELECT
            s.description  AS sec_desc,
            pg.description AS pg_desc,
            pt.description AS pt_desc
         FROM section s
         LEFT JOIN prob_group pg ON pg.section = s.section AND pg.prob_group = ?
         LEFT JOIN probtype   pt ON pt.section = s.section AND pt.prob_group = ? AND pt.prob_type = ?
         WHERE s.section = ?",
        [$prob_group, $prob_group, $prob_type, $section]
    );
    if ($pt) {
        $nama_section    = formatCodeDesc($section,    $pt['sec_desc'] ? descSlashToDash($pt['sec_desc']) : '');
        $nama_prob_group = formatCodeDesc($prob_group, $pt['pg_desc']  ? descSlashToDash($pt['pg_desc'])  : '');
        $nama_prob_type  = formatCodeDesc($prob_type,  $pt['pt_desc']  ? descSlashToDash($pt['pt_desc'])  : '');
    }
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

$type_req_labels = getTypeReqLabels();
$type_req_val   = trim($wr['type_req'] ?? '');
$type_req_label = $type_req_labels[$type_req_val] ?? '-';

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
