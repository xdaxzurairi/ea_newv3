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
    if ($r) $nama_negeri = trim($r['name']);
}
if ($neg_for_lookup && $kmp_for_lookup) {
    $r = dbFetchOne("SELECT state_id, site_id, name FROM site WHERE state_id = ? AND site_id = ?", [$neg_for_lookup, $kmp_for_lookup]);
    if ($r) $nama_kampus = trim($r['name']);
}
if ($neg_for_lookup && $kmp_for_lookup && $bng_for_lookup) {
    $r = dbFetchOne("SELECT bl_name, state_id, site_id, bldg_id FROM bldg WHERE state_id = ? AND site_id = ? AND bldg_id = ?", [$neg_for_lookup, $kmp_for_lookup, $bng_for_lookup]);
    if ($r) $nama_bangunan = trim($r['bl_name']);
}
if ($neg_for_lookup && $kmp_for_lookup && $bng_for_lookup && $bl_for_lookup) {
    $r = dbFetchOne("SELECT site_id, state_id, bl_id, bldg_id, name, block_num FROM bl WHERE state_id = ? AND site_id = ? AND bldg_id = ? AND bl_id = ?", [$neg_for_lookup, $kmp_for_lookup, $bng_for_lookup, $bl_for_lookup]);
    if ($r) $nama_blok = trim($r['name']);
}
if ($bl_for_lookup && $fl_for_lookup) {
    $r = dbFetchOne("SELECT fl_id, name FROM fl WHERE bl_id = ? AND fl_id = ?", [$bl_for_lookup, $fl_for_lookup]);
    if ($r) $nama_aras = trim($r['name']);
}
if ($rm_for_lookup && $bl_for_lookup && $fl_for_lookup) {
    $r = dbFetchOne("SELECT rm_id, bl_id, fl_id, name FROM rm WHERE rm_id = ? AND bl_id = ? AND fl_id = ?", [$rm_for_lookup, $bl_for_lookup, $fl_for_lookup]);
    if ($r) $nama_ruang = trim($r['name']);
}
if ($section) {
    $r = dbFetchOne("SELECT section, description FROM section WHERE section = ?", [$section]);
    if ($r) $nama_section = descSlashToDash($r['description']);
}
if ($section && $prob_group) {
    $r = dbFetchOne("SELECT section, prob_group, description FROM prob_group WHERE section = ? AND prob_group = ?", [$section, $prob_group]);
    if ($r) $nama_prob_group = descSlashToDash($r['description']);
}
if ($section && $prob_group && $prob_type) {
    $r = dbFetchOne("SELECT section, prob_group, prob_type, description FROM probtype WHERE section = ? AND prob_group = ? AND prob_type = ?", [$section, $prob_group, $prob_type]);
    if ($r) $nama_prob_type = descSlashToDash($r['description']);
}
if ($idkat_infra) {
    $r = dbFetchOne("SELECT infra_id, infra_name FROM wr_kat_infra WHERE infra_id = ?", [$idkat_infra]);
    if ($r) $nama_infra = trim($r['infra_name']);
}
if ($id_subinfra) {
    $r = dbFetchOne("SELECT SUBINFRA_ID, SUBINFRA_NAME FROM WR_SUB_INFRA WHERE subinfra_id = ?", [$id_subinfra]);
    if ($r) $nama_subinfra = trim($r['SUBINFRA_NAME']);
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
    // foto filenames diisi oleh JS sebelum submit
    for ($fi = 0; $fi < 3; $fi++) {
        echo '<input type="hidden" name="foto_' . $fi . '" id="hfoto_' . $fi . '" value="">' . "\n";
    }
    echo '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">' . "\n";
    ?>
    <!-- ===== GAMBAR KEROSAKAN ===== -->
    <div class="ea-card mb-3">
        <div class="ea-sec-hdr"><i class="bi bi-camera"></i> 4.0 Gambar Kerosakan <small class="fw-normal text-muted" style="font-size:0.8rem;">(pilihan &bull; maks. 3 gambar &bull; 5MB setiap satu)</small></div>
        <div class="card-body p-3">
            <p class="text-muted mb-2" style="font-size:0.85rem;"><i class="bi bi-info-circle"></i> Anda boleh mengambil gambar menggunakan kamera telefon atau memilih dari galeri.</p>
            <div class="row g-2" id="foto-preview-row">
                <?php for ($si = 0; $si < 3; $si++): ?>
                <div class="col-4">
                    <div class="foto-slot border rounded d-flex flex-column align-items-center justify-content-center bg-light position-relative"
                         style="height:130px;cursor:pointer;" id="slot-<?php echo $si; ?>" onclick="triggerFoto(<?php echo $si; ?>)">
                        <i class="bi bi-camera-plus" style="font-size:2.2rem;color:#aaa;" id="icon-<?php echo $si; ?>"></i>
                        <small class="text-muted mt-1" id="lbl-<?php echo $si; ?>">Gambar <?php echo $si + 1; ?></small>
                        <img id="preview-<?php echo $si; ?>" src="" alt=""
                             class="d-none position-absolute top-0 start-0 w-100 h-100"
                             style="object-fit:cover;border-radius:0.375rem;">
                        <button type="button"
                                class="btn btn-sm btn-danger position-absolute top-0 end-0 d-none"
                                id="remove-<?php echo $si; ?>"
                                style="padding:1px 6px;font-size:0.7rem;margin:3px;z-index:5;"
                                onclick="removeFoto(event,<?php echo $si; ?>)">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <input type="file" id="file-<?php echo $si; ?>" accept="image/*" capture="environment"
                           class="d-none" onchange="handleFoto(<?php echo $si; ?>)">
                </div>
                <?php endfor; ?>
            </div>
            <div id="foto-status" class="mt-2" style="font-size:0.82rem;min-height:1.2em;"></div>
        </div>
    </div>
    <!-- ===== AKHIR GAMBAR ===== -->

    <div class="d-flex justify-content-center gap-3 mb-4">
        <button type="button" class="btn btn-uitm-gold px-4" onclick="history.back();">
            <i class="bi bi-arrow-left-circle"></i> Kembali
        </button>
        <button type="button" class="btn btn-uitm px-5" onclick="hantarAduan();">
            <i class="bi bi-send"></i> Hantar Aduan
        </button>
    </div>
</form>

<script>
var BASE = '<?php echo baseUrl(); ?>';
var fotoUploading = false;

function triggerFoto(index) {
    if (fotoUploading) return;
    document.getElementById('file-' + index).click();
}

function handleFoto(index) {
    var fileInput = document.getElementById('file-' + index);
    var file = fileInput.files[0];
    if (!file) return;

    fotoUploading = true;
    var statusEl = document.getElementById('foto-status');
    statusEl.innerHTML = '<span class="text-muted"><i class="bi bi-hourglass-split"></i> Memuat naik Gambar ' + (index + 1) + '...</span>';

    var formData = new FormData();
    formData.append('foto', file);
    formData.append('index', index);

    fetch(BASE + '/ajax/upload_foto.php', { method: 'POST', body: formData })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        fotoUploading = false;
        if (data.success) {
            document.getElementById('hfoto_' + index).value = data.filename;
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = document.getElementById('preview-' + index);
                img.src = e.target.result;
                img.classList.remove('d-none');
                document.getElementById('icon-' + index).classList.add('d-none');
                document.getElementById('lbl-' + index).classList.add('d-none');
                document.getElementById('remove-' + index).classList.remove('d-none');
            };
            reader.readAsDataURL(file);
            var kb = data.size_kb ? ' (' + data.size_kb + ' KB)' : '';
            statusEl.innerHTML = '<span class="text-success"><i class="bi bi-check-circle-fill"></i> Gambar ' + (index + 1) + ' berjaya dimuat naik' + kb + '.</span>';
        } else {
            statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> ' + data.error + '</span>';
            fileInput.value = '';
        }
    })
    .catch(function() {
        fotoUploading = false;
        statusEl.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ralat sambungan. Sila cuba lagi.</span>';
        fileInput.value = '';
    });
}

function removeFoto(event, index) {
    event.stopPropagation();
    document.getElementById('hfoto_' + index).value = '';
    document.getElementById('file-' + index).value = '';
    var img = document.getElementById('preview-' + index);
    img.src = '';
    img.classList.add('d-none');
    document.getElementById('icon-' + index).classList.remove('d-none');
    document.getElementById('lbl-' + index).classList.remove('d-none');
    document.getElementById('remove-' + index).classList.add('d-none');
    document.getElementById('foto-status').innerHTML = '';
}

function hantarAduan() {
    if (fotoUploading) {
        document.getElementById('foto-status').innerHTML =
            '<span class="text-warning"><i class="bi bi-hourglass-split"></i> Sila tunggu gambar selesai dimuat naik.</span>';
        return;
    }
    document.getElementById('formSimpan').submit();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

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
