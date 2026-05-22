<?php
/**
 * eAduan v3 – Pengesahan Aduan
 * Terima POST dari borang.php, papar semua data untuk pengesahan sebelum simpan.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

validateCsrf();

$requestor    = post('requestor', sess('requestor', '1'));
$txt_emnumber = post('txt_emnumber', sess('txt_emnumber', ''));
$no_stud      = post('no_stud', sess('no_stud', ''));
$no_kp        = post('no_kp', sess('no_kp', ''));

$location     = post('location', '8');
$infra        = post('infra', '1');
$idnegeri     = post('idnegeri', '');
$idkampus     = post('idkampus', '');
$idbangunan   = post('idbangunan', '');
$idblok       = post('idblok', '');
$idfloor      = post('idfloor', '');
$idrm         = post('idrm', '');
$idkat_infra  = post('idkat_infra', '');
$id_subinfra  = post('id_subinfra', '');
$section      = post('section', '');
$prob_group   = post('prob_group', '');
$prob_type    = post('prob_type', '');
$prob_description = post('prob_description', '');
$loc_desc     = post('loc_desc', '');
$koordinat_lokasi = post('koordinat_lokasi', '');

$idneg   = post('idneg', '');
$idkamp  = post('idkamp', '');
$idbang  = post('idbang', '');
$idblk   = post('idblk', '');
$idfl    = post('idfl', '');
$idruang = post('idruang', '');
$neg     = post('neg', '');
$kamp    = post('kamp', '');
$bang    = post('bang', '');
$blk     = post('blk', '');
$fl      = post('fl', '');
$ruang   = post('ruang', '');

$foto_saved = [
    post('foto_0', ''),
    post('foto_1', ''),
    post('foto_2', ''),
];

// ---- Maklumat Pelapor ----
$pelapor = [];

if ($requestor == '1') {
    $row = dbFetchOne(
        "SELECT em_number, em_id, em_std, bl_id, fl_id, rm_id, dv_id, id_dept, extension, phone, email, image_file, hp_number FROM em WHERE em_number = ?",
        [$txt_emnumber]
    );
    if ($row) {
        $jwtn = dbFetchOne("SELECT description FROM emstd WHERE em_std = ?", [trim($row['em_std'])]);
        $dp   = dbFetchOne("SELECT dp_id, name FROM dp WHERE dp_id = ?", [trim($row['id_dept'])]);
        $pelapor = [
            'Nama Staf'            => trim($row['em_id']),
            'No. Staf'             => trim($row['em_number']),
            'Jawatan'              => $jwtn ? trim($jwtn['description']) : '',
            'Jabatan/Bahagian'     => $dp ? trim($dp['name']) : '',
            'No. Ruang'            => trim($row['bl_id'] . $row['fl_id'] . $row['rm_id']),
            'No. Telefon (pej.)'   => trim($row['phone']),
            'No. Telefon (bimbit)' => trim($row['hp_number']),
            'E-mail'               => trim($row['email']),
        ];
    }
} elseif ($requestor == '2') {
    $row = dbFetchOne(
        "SELECT stu_id, stu_name, program_id, stu_faclt, stu_phone, stu_address, stu_campus, dv_id, email, desc_campus FROM stu WHERE stu_id = ?",
        [$no_stud]
    );
    if ($row) {
        $fak  = dbFetchOne("SELECT nama_fakulti FROM fakulti WHERE kod_fakulti = ?", [trim($row['stu_faclt'])]);
        $site = dbFetchOne("SELECT name FROM site WHERE state_id = ?", [trim($row['stu_campus'])]);
        $pelapor = [
            'Nama Pelajar' => trim($row['stu_name']),
            'No. Pelajar'  => trim($row['stu_id']),
            'Fakulti'      => $fak ? trim($fak['nama_fakulti']) : '',
            'No. Telefon'  => trim($row['stu_phone']),
            'Program'      => trim($row['program_id']),
            'Kampus'       => $site ? trim($site['name']) : '',
            'E-mail'       => trim($row['email']),
        ];
        
    }
} elseif ($requestor == '3') {
    $row = dbFetchOne("SELECT email, tel_no, name_user, ic_no FROM afm_user_osd WHERE ic_no = ?", [$no_kp]);
    if ($row) {
        $pelapor = [
            'Nama'        => trim($row['name_user']),
            'No. K/P'     => trim($row['ic_no']),
            'No. Telefon' => trim($row['tel_no']),
            'E-mail'      => trim($row['email']),
        ];
    }
}

// ---- Resolve lokasi IDs to names ----
$nama_negeri = $nama_kampus = $nama_bangunan = $nama_blok = $nama_aras = $nama_ruang = '-';
$nama_section = $nama_prob_group = $nama_prob_type = '-';
$nama_infra = $nama_subinfra = '-';

if ($location == '8') {
    // neg/kamp/bang/blk/fl/ruang = ID sebenar, idneg/idkamp/idbang/idblk/idfl/idruang = nama paparan
    $nama_negeri   = $idneg;
    $nama_kampus   = $idkamp;
    $nama_bangunan = $idbang;
    $nama_blok     = $idblk;
    $nama_aras     = $idfl;
    $nama_ruang    = $idruang;

    $neg_for_lookup = $neg;
    $kmp_for_lookup = $kamp;
    $bng_for_lookup = $bang;
    $bl_for_lookup  = $blk;
    $fl_for_lookup  = $fl;
    $rm_for_lookup  = $ruang;
} else {
    $neg_for_lookup = $idnegeri;
    $kmp_for_lookup = $idkampus;
    $bng_for_lookup = $idbangunan;
    $bl_for_lookup  = $idblok;
    $fl_for_lookup  = $idfloor;
    $rm_for_lookup  = $idrm;
}

if ($neg_for_lookup) {
    $r = dbFetchOne("SELECT name, state_id FROM state WHERE state_id = ?", [$neg_for_lookup]);
    $nama_negeri = formatCodeDesc($neg_for_lookup, $r ? trim($r['name']) : ($location == '8' ? $idneg : ''));
}
if ($neg_for_lookup && $kmp_for_lookup) {
    $r = dbFetchOne("SELECT state_id, site_id, name FROM site WHERE state_id = ? AND site_id = ?", [$neg_for_lookup, $kmp_for_lookup]);
    $nama_kampus = formatCodeDesc($kmp_for_lookup, $r ? trim($r['name']) : ($location == '8' ? $idkamp : ''));
}
if ($neg_for_lookup && $kmp_for_lookup && $bng_for_lookup) {
    $r = dbFetchOne("SELECT bl_name, state_id, site_id, bldg_id FROM bldg WHERE state_id = ? AND site_id = ? AND bldg_id = ?", [$neg_for_lookup, $kmp_for_lookup, $bng_for_lookup]);
    $nama_bangunan = formatCodeDesc($bng_for_lookup, $r ? trim($r['bl_name']) : ($location == '8' ? $idbang : ''));
}
if ($neg_for_lookup && $kmp_for_lookup && $bng_for_lookup && $bl_for_lookup) {
    $r = dbFetchOne("SELECT site_id, state_id, bl_id, bldg_id, name, block_num FROM bl WHERE state_id = ? AND site_id = ? AND bldg_id = ? AND bl_id = ?", [$neg_for_lookup, $kmp_for_lookup, $bng_for_lookup, $bl_for_lookup]);
    $nama_blok = formatCodeDesc($bl_for_lookup, $r ? trim($r['name']) : ($location == '8' ? $idblk : ''));
}
if ($bl_for_lookup && $fl_for_lookup) {
    $r = dbFetchOne("SELECT fl_id, name FROM fl WHERE bl_id = ? AND fl_id = ?", [$bl_for_lookup, $fl_for_lookup]);
    $nama_aras = formatCodeDesc($fl_for_lookup, $r ? trim($r['name']) : ($location == '8' ? $idfl : ''));
}
if ($rm_for_lookup && $bl_for_lookup && $fl_for_lookup) {
    $r = dbFetchOne("SELECT rm_id, bl_id, fl_id, name FROM rm WHERE rm_id = ? AND bl_id = ? AND fl_id = ?", [$rm_for_lookup, $bl_for_lookup, $fl_for_lookup]);
    $nama_ruang = formatCodeDesc($rm_for_lookup, $r ? trim($r['name']) : ($location == '8' ? $idruang : ''));
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
if ($idkat_infra) {
    $r = dbFetchOne("SELECT infra_id, infra_name FROM wr_kat_infra WHERE infra_id = ?", [$idkat_infra]);
    $nama_infra = formatCodeDesc($idkat_infra, $r ? trim($r['infra_name']) : '');
}
if ($id_subinfra) {
    $r = dbFetchOne("SELECT SUBINFRA_ID, SUBINFRA_NAME FROM WR_SUB_INFRA WHERE subinfra_id = ?", [$id_subinfra]);
    $nama_subinfra = formatCodeDesc($id_subinfra, $r ? trim($r['SUBINFRA_NAME']) : '');
}

$mediumLabels = getMediumLabels();
$medium = post('medium', '1');

$pageTitle = 'Pengesahan Aduan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ea-card">
    <div class="ea-sec-hdr"><i class="bi bi-clipboard-check"></i> Pengesahan Maklumat Aduan</div>
    <div class="card-body p-3">
        <div class="alert alert-warning d-flex align-items-center mb-3" role="alert" style="font-size:0.85rem;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Sila semak semua maklumat di bawah sebelum menghantar aduan.
        </div>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-person"></i> 1.0 Maklumat Pelapor</div>
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
        </table>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-geo-alt"></i> 2.0 Lokasi Kerosakan</div>
        <table class="table table-borderless ea-table mb-3" style="font-size:0.88rem;">
            <tr><td class="lbl">Negeri</td><td><?php echo e($nama_negeri); ?></td></tr>
            <tr><td class="lbl">Kampus</td><td><?php echo e($nama_kampus); ?></td></tr>
            <tr><td class="lbl">Bangunan</td><td><?php echo e($nama_bangunan); ?></td></tr>
            <tr><td class="lbl">Blok</td><td><?php echo e($nama_blok); ?></td></tr>
            <?php if ($infra == '2'): ?>
            <tr><td class="lbl">Kategori Infra</td><td><?php echo e($nama_infra); ?></td></tr>
            <tr><td class="lbl">Sub Kategori Infra</td><td><?php echo e($nama_subinfra); ?></td></tr>
            <?php else: ?>
            <tr><td class="lbl">Aras</td><td><?php echo e($nama_aras); ?></td></tr>
            <tr><td class="lbl">Ruang</td><td><?php echo e($nama_ruang); ?></td></tr>
            <?php endif; ?>
            <?php if ($loc_desc): ?>
            <tr><td class="lbl">Keterangan Lokasi</td><td><?php echo e($loc_desc); ?></td></tr>
            <?php endif; ?>
            <?php if ($koordinat_lokasi): ?>
            <tr><td class="lbl">Koordinat</td><td><?php echo e($koordinat_lokasi); ?></td></tr>
            <tr>
                <td class="lbl align-top">Peta Lokasi</td>
                <td>
                    <div id="map_confirm" style="height:260px;border-radius:var(--radius-md);border:1.5px solid #dee2e6;"></div>
                </td>
            </tr>
            <?php endif; ?>
        </table>

        <div class="ea-sec-hdr mb-2"><i class="bi bi-tools"></i> 3.0 Jenis Kerosakan</div>
        <table class="table table-borderless ea-table mb-3" style="font-size:0.88rem;">
            <tr><td class="lbl text-uitm-purple">Seksyen</td><td><?php echo e($nama_section); ?></td></tr>
            <tr><td class="lbl">Elemen</td><td><?php echo e($nama_prob_group); ?></td></tr>
            <tr><td class="lbl">Masalah</td><td><?php echo e($nama_prob_type); ?></td></tr>
            <tr><td class="lbl">Keterangan</td><td><?php echo e($prob_description); ?></td></tr>
            <tr><td class="lbl">Medium</td><td><?php echo e($mediumLabels[$medium] ?? $medium); ?></td></tr>
        </table>

        <?php
        $has_foto_confirm = false;
        for ($fx = 0; $fx < 3; $fx++) {
            if (!empty($foto_saved[$fx])) {
                $has_foto_confirm = true;
                break;
            }
        }
        if ($has_foto_confirm): ?>
        <div class="ea-sec-hdr mb-2"><i class="bi bi-camera"></i> 4.0 Gambar Kerosakan</div>
        <div class="row g-2 mb-0">
            <?php for ($fx = 0; $fx < 3; $fx++):
                if (empty($foto_saved[$fx])) continue; ?>
            <div class="col-4">
                <?php $foto_url = baseUrl() . '/ajax/foto_temp.php?f=' . urlencode($foto_saved[$fx]); ?>
                <img src="<?php echo e($foto_url); ?>"
                     class="img-fluid rounded border"
                     style="height:120px;width:100%;object-fit:cover;"
                     alt="Gambar <?php echo $fx + 1; ?>">
                <div class="text-center mt-1" style="font-size:0.78rem;color:#666;">Gambar <?php echo $fx + 1; ?></div>
            </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<form id="formSimpan" method="post" action="<?php echo baseUrl(); ?>/pages/simpan.php">
    <?php
    $fields = [
        'requestor', 'txt_emnumber', 'no_stud', 'no_kp',
        'location', 'infra', 'idnegeri', 'idkampus', 'idbangunan', 'idblok',
        'idfloor', 'idrm', 'idkat_infra', 'id_subinfra',
        'section', 'prob_group', 'prob_type', 'prob_description', 'loc_desc',
        'koordinat_lokasi', 'medium',
        'idneg', 'idkamp', 'idbang', 'idblk', 'idfl', 'idruang',
        'neg', 'kamp', 'bang', 'blk', 'fl', 'ruang',
    ];
    foreach ($fields as $f) {
        echo '<input type="hidden" name="' . e($f) . '" value="' . e($$f) . '">' . "\n";
    }
    for ($fi = 0; $fi < 3; $fi++) {
        echo '<input type="hidden" name="foto_' . $fi . '" value="' . e($foto_saved[$fi] ?? '') . '">' . "\n";
    }
    echo '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">' . "\n";
    ?>

    <div class="d-flex justify-content-center gap-3 mb-4">
        <button type="button" class="btn btn-uitm-gold px-4" onclick="history.back();">
            <i class="bi bi-arrow-left-circle"></i> Kembali
        </button>
        <button type="button" class="btn btn-uitm px-5" onclick="hantarAduan();">
            <i class="bi bi-send"></i> Hantar Aduan
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
function hantarAduan() {
    document.getElementById('formSimpan').submit();
}
</script>

<?php if ($koordinat_lokasi): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
    var coord = '<?php echo e($koordinat_lokasi); ?>';
    if (!coord) return;
    var parts = coord.split(',');
    if (parts.length !== 2) return;
    var lat = parseFloat(parts[0]), lng = parseFloat(parts[1]);
    if (isNaN(lat) || isNaN(lng)) return;

    var map = L.map('map_confirm').setView([lat, lng], 17);
    L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0','mt1','mt2','mt3'],
        attribution: '&copy; <a href="https://maps.google.com">Google</a>'
    }).addTo(map);
    L.marker([lat, lng], {clickable:false, draggable:false}).addTo(map);
})();
</script>
<?php endif; ?>
