<?php
/**
 * Borang Aduan Utama – eAduan v3
 * Semua SQL kekal sama seperti sistem lama.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (empty($_SESSION['sso_authenticated'])) {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validateCsrf();
}

$pdo = getPdo();

$requestor    = sess('requestor', '1');
$txt_emnumber = sess('txt_emnumber', '');
$no_stud      = sess('no_stud', '');
$no_kp        = sess('no_kp', '');

$location   = req('location', '');
$infra      = req('infra', '');
$idnegeri   = req('idnegeri', '');
$idkampus   = req('idkampus', '');
$idbangunan = req('idbangunan', '');
$idblok     = req('idblok', '');
$idfloor    = req('idfloor', '');
$idrm       = req('idrm', '');
$idkat_infra = req('idkat_infra', '');
$id_subinfra = req('id_subinfra', '');
$section    = req('section', '');
$prob_group = req('prob_group', '');
$prob_type  = req('prob_type', '');
$loc_desc   = req('loc_desc', '');
$prob_description = req('prob_description', '');
$koordinat_lokasi = req('koordinat_lokasi', '');

$foto_saved = [
    req('foto_0', ''),
    req('foto_1', ''),
    req('foto_2', ''),
];

// Bilik sendiri vars (readonly, daripada popup atau auto-isi)
$idneg   = req('idneg', '');
$idkamp  = req('idkamp', '');
$idbang  = req('idbang', '');
$idblk   = req('idblk', '');
$idfl    = req('idfl', '');
$idruang = req('idruang', '');
$neg     = req('neg', '');
$kamp    = req('kamp', '');
$bang    = req('bang', '');
$blk     = req('blk', '');
$fl      = req('fl', '');
$ruang   = req('ruang', '');

// ---- Auto-isi Bilik Sendiri ----
$bilik = null;
if ($location == '8' && $requestor == '1' && $txt_emnumber) {
    $em_rooms = dbFetchAll("SELECT bl_id, fl_id, rm_id FROM em WHERE em_number = ?", [$txt_emnumber]);
    $valid_rooms = [];
    foreach ($em_rooms as $er) {
        if (trim($er['bl_id']) !== '' && trim($er['fl_id']) !== '' && trim($er['rm_id']) !== '') {
            $valid_rooms[] = $er;
        }
    }

    if (count($valid_rooms) == 1) {
        $s_bl = trim($valid_rooms[0]['bl_id']);
        $s_fl = trim($valid_rooms[0]['fl_id']);
        $s_rm = trim($valid_rooms[0]['rm_id']);

        $bl_row    = dbFetchOne("SELECT bl_id, name, state_id, site_id, bldg_id FROM bl WHERE bl_id = ?", [$s_bl]);
        $state_row = $bl_row ? dbFetchOne("SELECT state_id, name FROM state WHERE state_id = ?", [trim($bl_row['state_id'])]) : null;
        $site_row  = $bl_row ? dbFetchOne("SELECT site_id, name FROM site WHERE site_id = ?", [trim($bl_row['site_id'])]) : null;
        $bldg_row  = $bl_row ? dbFetchOne("SELECT bldg_id, bl_name FROM bldg WHERE bldg_id = ?", [trim($bl_row['bldg_id'])]) : null;
        $fl_row2   = dbFetchOne("SELECT fl_id, name FROM fl WHERE fl_id = ?", [$s_fl]);
        $rm_row2   = dbFetchOne("SELECT rm_id, bl_id, fl_id, name FROM rm WHERE rm_id = ? AND bl_id = ? AND fl_id = ?", [$s_rm, $s_bl, $s_fl]);

        $bilik = [
            'state_id' => $state_row ? trim($state_row['state_id']) : '',
            'namest'   => $state_row ? trim($state_row['name']) : '',
            'site_id'  => $site_row ? trim($site_row['site_id']) : '',
            'namesite' => $site_row ? trim($site_row['name']) : '',
            'bldg_id'  => $bldg_row ? trim($bldg_row['bldg_id']) : '',
            'builname' => $bldg_row ? trim($bldg_row['bl_name']) : '',
            'bl_id'    => $s_bl,
            'namebl'   => $bl_row ? trim($bl_row['name']) : '',
            'fl_id'    => $s_fl,
            'namefl'   => $fl_row2 ? trim($fl_row2['name']) : '',
            'rm_id'    => $s_rm,
            'namerm'   => $rm_row2 ? trim($rm_row2['name']) : '',
        ];

        $neg   = $bilik['state_id'];  $idneg   = $bilik['namest'];
        $kamp  = $bilik['site_id'];   $idkamp  = $bilik['namesite'];
        $bang  = $bilik['bldg_id'];   $idbang  = $bilik['builname'];
        $blk   = $bilik['bl_id'];     $idblk   = $bilik['namebl'];
        $fl    = $bilik['fl_id'];     $idfl    = $bilik['namefl'];
        $ruang = $bilik['rm_id'];     $idruang = $bilik['namerm'];
        $idkampus = $bilik['site_id'];
    }
    $bilik_count = count($valid_rooms);
}

// ---- Maklumat Pelapor ----
$pelapor = [];

if ($requestor == '1') {
    $qry_no = "SELECT em_number, em_id, em_std, bl_id, fl_id, rm_id, dv_id, id_dept, extension, phone, email, image_file, hp_number 
               FROM em WHERE em_number = ?";
    $row = dbFetchOne($qry_no, [$txt_emnumber]);
    if ($row) {
        $no_em   = trim($row['em_number']);
        $em_id   = trim($row['em_id']);
        $em_std  = trim($row['em_std']);
        $bl_id   = trim($row['bl_id']);
        $fl_id   = trim($row['fl_id']);
        $rm_id   = trim($row['rm_id']);
        $dv_id   = trim($row['dv_id']);
        $dp_id   = trim($row['id_dept']);
        $phone   = trim($row['phone']);
        $email   = trim($row['email']);
        $h_fon   = trim($row['hp_number']);

        $jwtn_row = dbFetchOne("SELECT description FROM emstd WHERE em_std = ?", [$em_std]);
        $jawatan  = $jwtn_row ? trim($jwtn_row['description']) : '';

        $dp_row   = dbFetchOne("SELECT dp_id, name FROM dp WHERE dp_id = ?", [$dp_id]);
        $jabatan  = $dp_row ? trim($dp_row['name']) : '';

        $space_row = dbFetchOne("SELECT bl_id, fl_id, rm_id FROM em WHERE em_number = ?", [$txt_emnumber]);
        $ruang_kod = '';
        $ruang_desc = '';
        if ($space_row) {
            $s_bl = trim($space_row['bl_id']);
            $s_fl = trim($space_row['fl_id']);
            $s_rm = trim($space_row['rm_id']);
            $ruang_kod = $s_bl . $s_fl . $s_rm;

            $bl_row = dbFetchOne("SELECT name FROM bl WHERE bl_id = ?", [$s_bl]);
            $fl_row = dbFetchOne("SELECT name FROM fl WHERE bl_id = ? AND fl_id = ?", [$s_bl, $s_fl]);
            $rm_row = dbFetchOne("SELECT name FROM rm WHERE bl_id = ? AND fl_id = ? AND rm_id = ?", [$s_bl, $s_fl, $s_rm]);

            $parts = array_filter([
                $bl_row ? trim($bl_row['name']) : '',
                $fl_row ? trim($fl_row['name']) : '',
                $rm_row ? trim($rm_row['name']) : '',
            ]);
            $ruang_desc = implode(' / ', $parts);
        }
        $no_ruang = $ruang_kod ? $ruang_kod . ($ruang_desc ? ' - ' . $ruang_desc : '') : '';

        $pelapor = [
            'Nama Staf'            => $em_id,
            'Jawatan'              => $jawatan,
            'Jabatan/Bahagian'     => $jabatan,
            'No. Ruang'            => $no_ruang,
            'No. Telefon (pej.)'   => $phone,
            'No. Telefon (bimbit)' => $h_fon,
            'E-mail'               => $email,
        ];
    }
} elseif ($requestor == '2') {
    $qry_stud = "SELECT stu_id, stu_name, program_id, stu_faclt, stu_phone, stu_address, stu_campus, dv_id, email, desc_campus
                 FROM stu WHERE stu_id = ?";
    $row = dbFetchOne($qry_stud, [$no_stud]);
    if ($row) {
        $stu_name    = trim($row['stu_name']);
        $stu_faclt   = trim($row['stu_faclt']);
        $stu_phone   = trim($row['stu_phone']);
        $stu_campus  = trim($row['stu_campus']);
        $email_stu   = trim($row['email']);
        $program_id  = trim($row['program_id']);

        $fak_row = dbFetchOne("SELECT nama_fakulti, kod_fakulti FROM fakulti WHERE kod_fakulti = ?", [$stu_faclt]);
        $nama_fakulti = $fak_row ? trim($fak_row['nama_fakulti']) : '';

        $site_row = dbFetchOne("SELECT state_id, site_id, name FROM site WHERE state_id = ?", [$stu_campus]);
        $nama_kampus = $site_row ? trim($site_row['name']) : '';

        $pelapor = [
            'Nama Pelajar' => $stu_name,
            'Fakulti'      => $nama_fakulti,
            'No. Telefon'  => $stu_phone,
            'Program'      => $program_id,
            'Kampus'       => $nama_kampus,
            'E-mail'       => $email_stu,
        ];
    }
} elseif ($requestor == '3') {
    $qry_luar = "SELECT email, tel_no, name_user, ic_no FROM afm_user_osd WHERE ic_no = ?";
    $row = dbFetchOne($qry_luar, [$no_kp]);
    if ($row) {
        $pelapor = [
            'Nama'         => trim($row['name_user']),
            'No. Telefon'  => trim($row['tel_no']),
            'E-mail'       => trim($row['email']),
        ];
    }
}

// ---- Label Infra ----
$inf_labels = ['negeri' => 'Negeri', 'kampus' => 'Kampus', 'bangunan' => 'Bangunan', 'blok' => 'Blok', 'aras' => 'Aras', 'ruang' => 'Ruang'];
if ($infra == '2') {
    $inf_labels['aras']  = 'Kategori Infra';
    $inf_labels['ruang'] = 'Sub Kategori Infra';
}

$pageTitle = 'Borang Aduan';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="ea-card">
    <div class="ea-sec-hdr"><i class="bi bi-clipboard-data"></i> 1.0 Maklumat Pelapor</div>
    <div class="card-body px-4 py-3">
        <div class="row g-2">
        <?php foreach ($pelapor as $label => $value): ?>
            <div class="col-md-6">
                <div class="ea-field">
                    <span class="ea-field-label"><?php echo e($label); ?></span>
                    <span class="ea-field-value"><?php echo e($value) ?: '<span class="text-muted">-</span>'; ?></span>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>

<form name="form1" id="form1" method="post" action="<?php echo baseUrl(); ?>/pages/borang.php">
    <input type="hidden" name="csrf_token" value="<?php echo e(csrfToken()); ?>">
    <input type="hidden" name="requestor" value="<?php echo e($requestor); ?>">
    <input type="hidden" name="txt_emnumber" value="<?php echo e($txt_emnumber); ?>">
    <input type="hidden" name="no_stud" value="<?php echo e($no_stud); ?>">
    <input type="hidden" name="no_kp" value="<?php echo e($no_kp); ?>">

    <input type="hidden" name="neg" id="neg" value="<?php echo e($neg); ?>">
    <input type="hidden" name="idneg" id="idneg" value="<?php echo e($idneg); ?>">
    <input type="hidden" name="kamp" id="kamp" value="<?php echo e($kamp); ?>">
    <input type="hidden" name="idkamp" id="idkamp" value="<?php echo e($idkamp); ?>">
    <input type="hidden" name="bang" id="bang" value="<?php echo e($bang); ?>">
    <input type="hidden" name="idbang" id="idbang" value="<?php echo e($idbang); ?>">
    <input type="hidden" name="blk" id="blk" value="<?php echo e($blk); ?>">
    <input type="hidden" name="idblk" id="idblk" value="<?php echo e($idblk); ?>">
    <input type="hidden" name="fl" id="fl" value="<?php echo e($fl); ?>">
    <input type="hidden" name="idfl" id="idfl" value="<?php echo e($idfl); ?>">
    <input type="hidden" name="ruang" id="ruang" value="<?php echo e($ruang); ?>">
    <input type="hidden" name="idruang" id="idruang" value="<?php echo e($idruang); ?>">

    <div class="ea-card">
        <div class="ea-sec-hdr"><i class="bi bi-geo-alt"></i> 2.0 Lokasi dan Jenis Kerosakan</div>
        <div class="card-body p-3 ea-form">

            <?php if ($requestor == '1'): ?>
            <div class="mb-3">
                <label class="form-label">Lokasi Kerosakan <span class="required">*</span></label>
                <div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="location" id="loc8" value="8"
                               <?php echo $location == '8' ? 'checked' : ''; ?> onchange="this.form.submit();">
                        <label class="form-check-label" for="loc8">Bilik Sendiri</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="location" id="loc9" value="9"
                               <?php echo $location == '9' ? 'checked' : ''; ?> onchange="this.form.submit();">
                        <label class="form-check-label" for="loc9">Lokasi Lain</label>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="location" value="9">
            <?php $location = '9'; ?>
            <?php endif; ?>

            <?php if ($location == '8'): ?>
            <!-- ====== BILIK SENDIRI ====== -->

            <?php if (isset($bilik_count) && $bilik_count == 0): ?>
            <div class="alert alert-warning py-2" style="font-size:0.85rem;">
                <i class="bi bi-exclamation-triangle"></i> Ruang bilik sendiri tidak/belum didaftarkan. Sila kemaskini maklumat ruang di
                <a href="https://fms.uitm.edu.my/eruang/ruang_staff2/" target="_blank">portal e-Ruang</a>, atau pilih <strong>Lokasi Lain</strong>.
            </div>

            <?php else: ?>
            <?php $has_room = ($bilik || $blk); ?>

            <?php if (isset($bilik_count) && $bilik_count > 1): ?>
            <div class="alert alert-info py-2 mb-2" style="font-size:0.85rem;">
                <i class="bi bi-info-circle"></i> Anda mempunyai <?php echo $bilik_count; ?> bilik berdaftar. Sila pilih bilik:
            </div>
            <button type="button" class="btn btn-uitm-gold btn-sm mb-3" onclick="popUpbilik();">
                <i class="bi bi-door-open"></i> Pilih Bilik Sendiri
            </button>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="ea-sec-hdr-gold mb-2"><i class="bi bi-house-door"></i> Bilik Sendiri</div>
                    <?php if ($has_room): ?>
                    <div class="row g-2">
                        <div class="col-6"><div class="ea-field"><span class="ea-field-label">Negeri</span><span class="ea-field-value"><?php echo e(formatCodeDesc($neg, $idneg)); ?></span></div></div>
                        <div class="col-6"><div class="ea-field"><span class="ea-field-label">Kampus</span><span class="ea-field-value"><?php echo e(formatCodeDesc($kamp, $idkamp)); ?></span></div></div>
                        <div class="col-6"><div class="ea-field"><span class="ea-field-label">Bangunan</span><span class="ea-field-value"><?php echo e(formatCodeDesc($bang, $idbang)); ?></span></div></div>
                        <div class="col-6"><div class="ea-field"><span class="ea-field-label">Blok</span><span class="ea-field-value"><?php echo e(formatCodeDesc($blk, $idblk)); ?></span></div></div>
                        <div class="col-6"><div class="ea-field"><span class="ea-field-label">Aras</span><span class="ea-field-value"><?php echo e(formatCodeDesc($fl, $idfl)); ?></span></div></div>
                        <div class="col-6"><div class="ea-field"><span class="ea-field-label">Ruang</span><span class="ea-field-value"><?php echo e(formatCodeDesc($ruang, $idruang)); ?></span></div></div>
                    </div>
                    <?php else: ?>
                    <p class="text-muted" style="font-size:0.85rem;">Sila pilih bilik dari senarai di atas.</p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <div class="ea-sec-hdr-gold mb-2"><i class="bi bi-tools"></i> Jenis Kerosakan</div>
                    <div class="mb-2">
                        <label class="form-label text-uitm-purple">Seksyen <span class="required">*</span></label>
                        <select name="section" id="section" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Elemen <span class="required">*</span></label>
                        <select name="prob_group" id="prob_group" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Masalah <span class="required">*</span></label>
                        <select name="prob_type" id="prob_type" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Keterangan Lokasi <span class="required">*</span></label>
                    <textarea name="loc_desc" class="form-control" rows="3"><?php echo e($loc_desc); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Keterangan Kerosakan <span class="required">*</span></label>
                    <textarea name="prob_description" class="form-control" rows="3"><?php echo e($prob_description); ?></textarea>
                </div>
            </div>

            <?php endif; ?>

            <?php elseif ($location == '9'): ?>
            <!-- ====== LOKASI LAIN ====== -->
            <div class="mb-3">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="infra" id="infra1" value="1"
                           <?php echo $infra == '1' ? 'checked' : ''; ?> onchange="this.form.submit();">
                    <label class="form-check-label" for="infra1">Dalam Bangunan</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="infra" id="infra2" value="2"
                           <?php echo $infra == '2' ? 'checked' : ''; ?> onchange="this.form.submit();">
                    <label class="form-check-label" for="infra2">Luar Bangunan</label>
                </div>
            </div>

            <?php if ($infra == '1' || $infra == '2'): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="ea-sec-hdr-gold mb-2"><i class="bi bi-geo-alt"></i> Lokasi</div>
                    <div class="mb-2">
                        <label class="form-label"><?php echo e($inf_labels['negeri']); ?> <span class="required">*</span></label>
                        <select name="idnegeri" id="idnegeri" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label"><?php echo e($inf_labels['kampus']); ?> <span class="required">*</span></label>
                        <select name="idkampus" id="idkampus" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label"><?php echo e($inf_labels['bangunan']); ?> <span class="required">*</span></label>
                        <select name="idbangunan" id="idbangunan" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label"><?php echo e($inf_labels['blok']); ?> <span class="required">*</span></label>
                        <select name="idblok" id="idblok" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label"><?php echo e($inf_labels['aras']); ?></label>
                        <?php if ($infra == '1'): ?>
                        <input type="hidden" name="idkat_infra" id="idkat_infra" value="">
                        <select name="idfloor" id="idfloor" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                        <?php else: ?>
                        <input type="hidden" name="idfloor" id="idfloor" value="">
                        <select name="idkat_infra" id="idkat_infra" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2">
                        <label class="form-label"><?php echo e($inf_labels['ruang']); ?></label>
                        <?php if ($infra == '1'): ?>
                        <input type="hidden" name="id_subinfra" id="id_subinfra" value="">
                        <select name="idrm" id="idrm" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                        <?php else: ?>
                        <input type="hidden" name="idrm" id="idrm" value="">
                        <select name="id_subinfra" id="id_subinfra" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                        <?php endif; ?>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan Lokasi <span class="required">*</span></label>
                        <textarea name="loc_desc" class="form-control" rows="3"><?php echo e($loc_desc); ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="ea-sec-hdr-gold mb-2"><i class="bi bi-tools"></i> Jenis Kerosakan</div>
                    <div class="mb-2">
                        <label class="form-label text-uitm-purple">Seksyen <span class="required">*</span></label>
                        <select name="section" id="section" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Elemen <span class="required">*</span></label>
                        <select name="prob_group" id="prob_group" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Masalah <span class="required">*</span></label>
                        <select name="prob_type" id="prob_type" class="form-select">
                            <option value="">-- Sila Pilih --</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Keterangan Kerosakan <span class="required">*</span></label>
                        <textarea name="prob_description" class="form-control" rows="3"><?php echo e($prob_description); ?></textarea>
                    </div>
                </div>
            </div>

            <?php if ($infra == '2'): ?>
            <div class="row mb-3">
                <div class="col-12">
                    <label class="form-label">Peta Lokasi</label>
                    <div id="map" style="height:380px;border-radius:var(--radius-md);border:1.5px solid #dee2e6;"></div>
                    <div class="mt-2 d-flex flex-wrap align-items-center gap-2" style="font-size:0.85rem;">
                        <span class="fw-semibold" style="color:var(--uitm-purple-dark);">Koordinat Lokasi:</span>
                        <input type="text" name="koordinat_lokasi" id="koordinat_lokasi"
                               class="form-control form-control-sm"
                               style="max-width:260px;"
                               value="<?php echo e($koordinat_lokasi); ?>" readonly>
                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="if (window.eaGetCurrentLocation) { window.eaGetCurrentLocation(); }">
                            Guna Lokasi Semasa
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block">Klik pada peta untuk tanda lokasi kerosakan.</small>
                    <small id="map-status" class="text-muted mt-1 d-block"></small>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; // infra check ?>
            <?php endif; // location check ?>

            <?php
            $foto_section_title = '3.0 Gambar Kerosakan';
            require __DIR__ . '/../includes/foto_upload_ui.php';
            ?>

            <div id="validation-alert-wrap"></div>
            <div class="text-center mt-3 pt-3 border-top">
                <button type="button" class="btn btn-uitm px-5" id="btnTeruskan"
                        onclick="validateAndSubmit();">
                    <i class="bi bi-arrow-right-circle"></i> Teruskan
                </button>
            </div>

            <p class="text-center text-secondary mt-2" style="font-size:0.82rem;">
                <i class="bi bi-info-circle"></i> <strong>Penafian:</strong> Saya mengaku bahawa segala maklumat aduan yang dikemukakan adalah benar dan saya bertanggungjawab ke atas aduan tersebut.
            </p>
        </div>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php if ($infra == '2'): ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
    var coordInput = document.getElementById('koordinat_lokasi');
    var statusEl = document.getElementById('map-status');
    var map, marker = null;

    function setCoord(lat, lng) {
        if (!coordInput) return;
        coordInput.value = lat.toFixed(6) + ',' + lng.toFixed(6);
    }

    function setStatus(message, type) {
        if (!statusEl) return;
        var cls = 'text-muted mt-1 d-block';
        if (type === 'success') cls = 'text-success mt-1 d-block';
        else if (type === 'error') cls = 'text-danger mt-1 d-block';
        statusEl.className = cls;
        statusEl.textContent = message || '';
    }

    // withInitialMarker = false untuk fallback (jangan auto isi koordinat palsu)
    function initMap(centerLat, centerLng, zoom, withInitialMarker) {
        map = L.map('map', {
            center: [centerLat, centerLng],
            zoom: zoom || 17,
            dragging: true
        });

        L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0','mt1','mt2','mt3'],
            attribution: '&copy; <a href="https://maps.google.com">Google</a>'
        }).addTo(map);

        if (withInitialMarker !== false) {
            marker = L.marker([centerLat, centerLng], {clickable: true, draggable: true}).addTo(map);
            setCoord(centerLat, centerLng);

            marker.on('dragend', function(e){
                var ll = e.target.getLatLng();
                setCoord(ll.lat, ll.lng);
            });
        }

        // Klik peta: cipta / ubah marker dan set koordinat
        map.on('click', function(e){
            var ll = e.latlng;
            if (!marker) {
                marker = L.marker(ll, {clickable: true, draggable: true}).addTo(map);
                marker.on('dragend', function(e2){
                    var ll2 = e2.target.getLatLng();
                    setCoord(ll2.lat, ll2.lng);
                });
            } else {
                marker.setLatLng(ll);
            }
            setCoord(ll.lat, ll.lng);
        });
    }

    // Jika sudah ada koordinat simpanan, guna sebagai permulaan
    if (coordInput && coordInput.value) {
        var parts = coordInput.value.split(',');
        if (parts.length === 2) {
            var lat0 = parseFloat(parts[0]);
            var lng0 = parseFloat(parts[1]);
            if (!isNaN(lat0) && !isNaN(lng0)) {
                // Koordinat dari aduan lepas – terus guna sebagai marker awal, zoom dekat
                initMap(lat0, lng0, 19, true);
                setStatus('Lokasi telah dipilih sebelum ini. Seret penanda atau klik "Guna Lokasi Semasa" jika mahu kemas kini.', 'success');
                return;
            }
        }
    }

    function applyUserLocation(lat, lng) {
        setCoord(lat, lng);
        if (map) {
            map.setView([lat, lng], 19);
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng], {clickable: true, draggable: true}).addTo(map);
            marker.on('dragend', function(e){
                var ll = e.target.getLatLng();
                setCoord(ll.lat, ll.lng);
            });
        } else {
            initMap(lat, lng, 19, true);
        }
    }

    function onGeoSuccess(position){
        var lat = position.coords.latitude;
        var lng = position.coords.longitude;
        applyUserLocation(lat, lng);
        setStatus('Lokasi peranti berjaya dikesan. Anda boleh seret penanda jika mahu ubah.', 'success');
    }

    function onGeoError(err){
        console.warn('Geolocation error', err);
        setStatus('', 'muted');
        // Peta tanpa koordinat awal; user boleh klik peta atau "Guna Lokasi Semasa"
        initMap(4.2105, 101.9758, 6, false);
    }

    // Jika tiada koordinat, cuba GPS peranti dengan high-accuracy
    if (navigator.geolocation) {
        setStatus('Sedang cuba mendapatkan lokasi semasa peranti...', 'default');
        navigator.geolocation.getCurrentPosition(onGeoSuccess, onGeoError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
    } else {
        setStatus('', 'muted');
        initMap(4.2105, 101.9758, 6, false);
    }

    // Fungsi global untuk butang "Guna Lokasi Semasa"
    window.eaGetCurrentLocation = function(){
        if (!navigator.geolocation) return;
        setStatus('Sedang cuba mendapatkan lokasi semasa peranti...', 'default');
        navigator.geolocation.getCurrentPosition(onGeoSuccess, onGeoError, {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        });
    };
})();
</script>
<?php endif; ?>

<script>
(function(){
    var BASE = '<?php echo baseUrl(); ?>';
    var infra = '<?php echo e($infra); ?>';
    var location = '<?php echo e($location); ?>';
    var savedIdkampus = '<?php echo e($idkampus); ?>';
    var savedSection = '<?php echo e($section); ?>';
    var savedProbGroup = '<?php echo e($prob_group); ?>';
    var savedProbType = '<?php echo e($prob_type); ?>';

    var _pendingLoads = 0;
    function _setTeruskanState(loading) {
        var btn = document.getElementById('btnTeruskan');
        if (!btn) return;
        if (loading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memuat...';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-right-circle"></i> Teruskan';
        }
    }

    function optionLabel(item) {
        var id = (item.id || '').trim();
        var name = (item.name || '').trim();
        if (id && name && id.toUpperCase() !== name.toUpperCase()) {
            return id + ' - ' + name;
        }
        return name || id;
    }

    function loadOptions(url, selectId, savedVal, callback) {
        var sel = document.getElementById(selectId);
        if (!sel) return;
        sel.innerHTML = '<option value="">Memuat...</option>';
        _pendingLoads++;
        _setTeruskanState(true);
        fetch(url).then(r => r.json()).then(data => {
            var html = '<option value="">-- Sila Pilih --</option>';
            data.forEach(function(item){
                var selected = (item.id == savedVal) ? ' selected' : '';
                html += '<option value="'+esc(item.id)+'"'+selected+'>'+esc(optionLabel(item))+'</option>';
            });
            sel.innerHTML = html;
            _pendingLoads--;
            if (_pendingLoads <= 0) { _pendingLoads = 0; _setTeruskanState(false); }
            if (callback && savedVal) callback();
        }).catch(function(){
            sel.innerHTML = '<option value="">Ralat</option>';
            _pendingLoads--;
            if (_pendingLoads <= 0) { _pendingLoads = 0; _setTeruskanState(false); }
        });
    }

    function esc(s){var d=document.createElement('div');d.textContent=s;return d.innerHTML;}

    if (location == '9' && (infra == '1' || infra == '2')) {
        // Negeri
        loadOptions(BASE+'/ajax/get_negeri.php', 'idnegeri', '<?php echo e($idnegeri); ?>', function(){
            loadKampus();
        });

        var neg = document.getElementById('idnegeri');
        if(neg) neg.addEventListener('change', function(){
            clearFrom('idkampus'); loadKampus();
        });

        function loadKampus() {
            var v = val('idnegeri'); if(!v) return;
            loadOptions(BASE+'/ajax/get_kampus.php?idnegeri='+v, 'idkampus', '<?php echo e($idkampus); ?>', function(){
                loadBangunan();
                loadSeksyen();
            });
        }
        var kp = document.getElementById('idkampus');
        if(kp) kp.addEventListener('change', function(){
            clearFrom('idbangunan'); loadBangunan(); loadSeksyen();
        });

        function loadBangunan(){
            var v = val('idkampus'); if(!v) return;
            loadOptions(BASE+'/ajax/get_bangunan.php?idkampus='+v, 'idbangunan', '<?php echo e($idbangunan); ?>', function(){loadBlok();});
        }
        bind('idbangunan','change',function(){clearFrom('idblok');loadBlok();});

        function loadBlok(){
            var v = val('idbangunan'); if(!v) return;
            loadOptions(BASE+'/ajax/get_blok.php?idbangunan='+v, 'idblok', '<?php echo e($idblok); ?>', function(){loadAras();});
        }
        bind('idblok','change',function(){clearAras();loadAras();});

        function loadAras(){
            var blk = val('idblok'); if(!blk) return;
            if(infra=='1'){
                loadOptions(BASE+'/ajax/get_aras.php?idblok='+blk, 'idfloor', '<?php echo e($idfloor); ?>', function(){loadRuang();});
            } else {
                loadOptions(BASE+'/ajax/get_kat_infra.php', 'idkat_infra', '<?php echo e($idkat_infra); ?>', function(){loadSubInfra();});
            }
        }
        if(infra=='1'){
            bind('idfloor','change',function(){resetSel('idrm');loadRuang();});
        } else {
            bind('idkat_infra','change',function(){resetSel('id_subinfra');loadSubInfra();});
        }

        function loadRuang(){
            var fl=val('idfloor'),blk=val('idblok'); if(!fl||!blk) return;
            loadOptions(BASE+'/ajax/get_ruang.php?idfloor='+fl+'&idblok='+blk, 'idrm', '<?php echo e($idrm); ?>');
        }
        function loadSubInfra(){
            var v=val('idkat_infra'); if(!v) return;
            loadOptions(BASE+'/ajax/get_sub_infra.php?idkat_infra='+v, 'id_subinfra', '<?php echo e($id_subinfra); ?>');
        }

        function loadSeksyen(){
            var k=val('idkampus'); if(!k) return;
            loadOptions(BASE+'/ajax/get_seksyen.php?idkampus='+k+'&infra='+infra, 'section', savedSection, function(){loadElemen();});
        }
        bind('section','change',function(){resetSel('prob_group');resetSel('prob_type');loadElemen();});

        function loadElemen(){
            var s=val('section'),k=val('idkampus'); if(!s||!k) return;
            loadOptions(BASE+'/ajax/get_elemen.php?section='+s+'&idkampus='+k, 'prob_group', savedProbGroup, function(){loadMasalah();});
        }
        bind('prob_group','change',function(){resetSel('prob_type');loadMasalah();});

        function loadMasalah(){
            var pg=val('prob_group'),k=val('idkampus'); if(!pg||!k) return;
            loadOptions(BASE+'/ajax/get_masalah.php?prob_group='+pg+'&idkampus='+k, 'prob_type', savedProbType);
        }
    }

    // Bilik sendiri – load seksyen & elemen when kampus is known
    if (location == '8') {
        var popKamp = '<?php echo e($kamp ?: $idkampus); ?>';
        if (popKamp) {
            loadOptions(BASE+'/ajax/get_seksyen.php?idkampus='+popKamp+'&infra=1', 'section', savedSection, function(){loadElemen8();});
        }
        bind('section','change',function(){resetSel('prob_group');resetSel('prob_type');loadElemen8();});
        function loadElemen8(){
            var s=val('section'); if(!s||!popKamp) return;
            loadOptions(BASE+'/ajax/get_elemen.php?section='+s+'&idkampus='+popKamp, 'prob_group', savedProbGroup, function(){loadMasalah8();});
        }
        bind('prob_group','change',function(){resetSel('prob_type');loadMasalah8();});
        function loadMasalah8(){
            var pg=val('prob_group'); if(!pg||!popKamp) return;
            loadOptions(BASE+'/ajax/get_masalah.php?prob_group='+pg+'&idkampus='+popKamp, 'prob_type', savedProbType);
        }
    }

    function val(id){var e=document.getElementById(id);return e?e.value:'';}
    function bind(id,ev,fn){var e=document.getElementById(id);if(e)e.addEventListener(ev,fn);}
    function resetSel(id){var e=document.getElementById(id);if(e)e.innerHTML='<option value="">-- Sila Pilih --</option>';}
    function clearFrom(startId){
        var ids=['idkampus','idbangunan','idblok','idfloor','idrm','idkat_infra','id_subinfra'];
        var found=false;
        ids.forEach(function(id){if(id==startId)found=true;if(found)resetSel(id);});
    }
    function clearAras(){resetSel('idfloor');resetSel('idrm');resetSel('idkat_infra');resetSel('id_subinfra');}
})();

function popUpbilik(){
    var em = '<?php echo e($txt_emnumber); ?>';
    var ns = '<?php echo e($no_stud); ?>';
    var rq = '<?php echo e($requestor); ?>';
    var loc = '<?php echo e($location); ?>';
    window.open('<?php echo baseUrl(); ?>/pages/bilik_sendiri.php?txt_emnumber='+em+'&no_stud='+ns+'&requestor='+rq+'&location='+loc, 'bilik', 'width=850,height=300,scrollbars=yes');
}

function validateAndSubmit() {
    if (typeof eaIsFotoUploading === 'function' && eaIsFotoUploading()) {
        var fs = document.getElementById('foto-status');
        if (fs) {
            fs.innerHTML = '<span class="text-warning"><i class="bi bi-hourglass-split"></i> Sila tunggu gambar selesai dimuat naik.</span>';
            fs.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    document.querySelectorAll('.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
    var wrap = document.getElementById('validation-alert-wrap');
    if (wrap) wrap.innerHTML = '';

    var errors = [];
    var loc = '<?php echo e($location); ?>';
    var inf = '<?php echo e($infra); ?>';
    var rq  = '<?php echo e($requestor); ?>';

    if (rq == '1' && !loc) {
        errors.push('Sila pilih Lokasi Kerosakan (Bilik Sendiri / Lokasi Lain)');
    }

    if (loc == '9' && !inf) {
        errors.push('Sila pilih jenis infrastruktur (Dalam Bangunan / Luar Bangunan)');
    }

    if (loc == '8') {
        var blkEl = document.getElementById('blk');
        if (!blkEl || !blkEl.value) {
            errors.push('Maklumat Bilik Sendiri belum lengkap. Sila pilih bilik.');
        }
        reqSelect('section', 'Seksyen', errors);
        reqSelect('prob_group', 'Elemen', errors);
        reqSelect('prob_type', 'Masalah', errors);
        reqText('loc_desc', 'Keterangan Lokasi', errors);
        reqText('prob_description', 'Keterangan Kerosakan', errors);
    }

    if (loc == '9' && (inf == '1' || inf == '2')) {
        reqSelect('idnegeri', 'Negeri', errors);
        reqSelect('idkampus', 'Kampus', errors);
        reqSelect('idbangunan', 'Bangunan', errors);
        reqSelect('idblok', 'Blok', errors);

        if (inf == '1') {
            reqSelect('idfloor', 'Aras', errors);
            reqSelect('idrm', 'Ruang', errors);
        } else {
            reqSelect('idkat_infra', 'Kategori Infra', errors);
            reqSelect('id_subinfra', 'Sub Kategori Infra', errors);
        }

        reqSelect('section', 'Seksyen', errors);
        reqSelect('prob_group', 'Elemen', errors);
        reqSelect('prob_type', 'Masalah', errors);
        reqText('loc_desc', 'Keterangan Lokasi', errors);
        reqText('prob_description', 'Keterangan Kerosakan', errors);

        if (inf == '2') {
            var coord = document.getElementById('koordinat_lokasi');
            if (!coord || !coord.value.trim()) {
                errors.push('Sila tandakan lokasi pada peta (Koordinat Lokasi)');
                if (coord) coord.classList.add('is-invalid');
            }
        }
    }

    if (errors.length > 0) {
        var html = '<div class="alert alert-danger py-2 mb-0" style="font-size:0.88rem;text-align:left;">' +
            '<i class="bi bi-exclamation-triangle-fill me-1"></i>' +
            '<strong>Sila lengkapkan maklumat berikut sebelum meneruskan:</strong>' +
            '<ul class="mb-0 mt-1 ps-3">' +
            errors.map(function(e){ return '<li>' + e + '</li>'; }).join('') +
            '</ul></div>';
        if (wrap) wrap.innerHTML = html;

        var firstInvalid = document.querySelector('.is-invalid');
        if (firstInvalid) {
            firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalid.focus();
        } else if (wrap) {
            wrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        return;
    }

    var form = document.getElementById('form1');
    form.action = '<?php echo baseUrl(); ?>/pages/info_aduan.php';
    form.submit();
}

function reqSelect(id, label, errors) {
    var el = document.getElementById(id);
    if (el && !el.value) {
        errors.push(label + ' belum dipilih');
        el.classList.add('is-invalid');
    }
}

function reqText(name, label, errors) {
    var el = document.querySelector('textarea[name="' + name + '"]');
    if (el && !el.value.trim()) {
        errors.push(label + ' belum diisi');
        el.classList.add('is-invalid');
    }
}

</script>
<script>window.EA_BASE = '<?php echo baseUrl(); ?>';</script>
<script src="<?php echo baseUrl(); ?>/assets/js/foto-upload.js"></script>
