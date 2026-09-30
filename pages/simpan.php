<?php
/**
 * eAduan v3 – Simpan Aduan
 * Terima POST dari info_aduan.php, masukkan ke jadual wr, hantar e-mel BOM.
 */
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/minio.php';

if (empty($_SESSION['sso_authenticated'])) {
    header('Location: ' . baseUrl() . '/index.php');
    exit;
}

validateCsrf();

$pdo = getPdo();

// Identiti pelapor WAJIB dari sesi — jangan percaya nilai POST (boleh dipalsukan)
$requestor    = (string) sess('requestor', '');
$txt_emnumber = (string) sess('txt_emnumber', '');
$no_stud      = (string) sess('no_stud', '');
$no_kp        = (string) sess('no_kp', '');
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
$medium       = post('medium', '1');

// Bilik sendiri: neg/kamp/bang/blk/fl/ruang = ID sebenar
$neg_val   = post('neg', '');
$kamp_val  = post('kamp', '');
$bang_val   = post('bang', '');
$blk_val   = post('blk', '');
$fl_val    = post('fl', '');
$ruang_val = post('ruang', '');

// ---- Determine bl_id, fl_id, rm_id, state_id, site_id ----
if ($location == '8') {
    $bl_id    = $blk_val ?: $idblok;
    $fl_id    = $fl_val ?: $idfloor;
    $rm_id    = $ruang_val ?: $idrm;
    $state_id = $neg_val ?: $idnegeri;
    $site_id  = $kamp_val ?: $idkampus;
} else {
    $bl_id    = $idblok;
    $fl_id    = $idfloor;
    $rm_id    = $idrm;
    $state_id = $idnegeri;
    $site_id  = $idkampus;
}

// Untuk lokasi lain dengan infra luar bangunan (infra == 2),
// fl_id dan rm_id mungkin tidak berkaitan. Samakan dengan sistem lama:
// jika kosong, hantar NULL supaya foreign key tidak konflik.
if ($location == '9' && $infra == '2') {
    if ($fl_id === '' || $fl_id === '0') {
        $fl_id = null;
    }
    if ($rm_id === '' || $rm_id === '0') {
        $rm_id = null;
    }
}

// ---- Zone lookup ----
// zone_id mesti wujud dalam afm.zone_uitm (FK wr_zone_id); jika kosong atau tidak sah, guna NULL
$zone_id = null;
if ($bl_id) {
    // Satu JOIN: ambil zone_id dari bl dan sahkan wujud dalam zone_uitm sekaligus
    $zr = dbFetchOne(
        "SELECT b.zone_id FROM bl b INNER JOIN afm.zone_uitm z ON z.zone_id = b.zone_id WHERE b.bl_id = ?",
        [$bl_id]
    );
    $zone_id = $zr ? trim($zr['zone_id']) : null;
}

// ---- Pelapor info for INSERT ----
$phone_val      = '';
$em_number      = '';
$stu_id_val     = '';
$ic_no_val      = '';
$hp_requestor   = '';
$type_req       = null;
$requestor_name = '';

if ($requestor == '1') {
    // Staf UiTM – bezakan staf biasa vs staf PPF (inisiatif)
    $em_row = dbFetchOne(
        "SELECT em_id,bl_id,fl_id,rm_id,phone,hp_number,em_number,email,dv_id,dp_id FROM em WHERE em_number = ?",
        [$txt_emnumber]
    );
    if ($em_row) {
        $phone_val      = trim($em_row['phone']);
        $em_number      = trim($em_row['em_number']);
        $requestor_name = trim($em_row['em_id']); // nama staf
    }
    // Jika dp_id staf adalah PPF (A0140 atau N00020), treat sebagai inisiatif (5)
       // Jika staf dalam cf table (in_house=1/2), juga treat sebagai inisiatif (5) — sama seperti pwa_eworks
       $dp_id = $em_row ? trim($em_row['dp_id'] ?? '') : '';
       if ($dp_id === 'A0140' || $dp_id === 'N00020') {
           $type_req = '5'; // Staf PPF
       } else {
           // Semak CF table — staf penyelia dalam cf juga dianggap inisiatif
           $cfRow = dbFetchOne(
               "SELECT c.cf_id FROM cf c
                INNER JOIN em e ON e.em_number = c.cf_id
                WHERE e.em_number = ? AND c.in_house = '1'",
               [$em_number]
           );
           $type_req = $cfRow ? '5' : '1';
       }

} elseif ($requestor == '2') {
    // Pelajar – kekalkan seperti eAduan v3 (type_req = 2)
    $stu_row = dbFetchOne("SELECT stu_phone, stu_id, stu_name, email FROM stu WHERE stu_id = ?", [$no_stud]);
    if ($stu_row) {
        $phone_val      = trim($stu_row['stu_phone']);
        $stu_id_val     = trim($stu_row['stu_id']);
        $requestor_name = trim($stu_row['stu_name']); // nama pelajar
    }
    $type_req = '2';

} elseif ($requestor == '3') {
    // Pengguna luar – sama seperti eAduan v3 (type_req = 3)
    $luar_row = dbFetchOne("SELECT tel_no, name_user, ic_no, email FROM afm_user_osd WHERE ic_no = ?", [$no_kp]);
    if ($luar_row) {
        $phone_val      = trim($luar_row['tel_no']);
        $hp_requestor   = trim($luar_row['tel_no']);
        $ic_no_val      = trim($luar_row['ic_no']);
        $requestor_name = trim($luar_row['name_user']); // nama pengguna luar
    }
    $type_req = '3';
}

// ---- Helper: simpan foto ke kolum wr (foto1/foto2/foto3) ----
function simpanFotoDB(PDO $pdo, int $wr_id): void
{
    // Tambah kolum jika belum wujud
    foreach (['foto1', 'foto2', 'foto3'] as $col) {
        try {
            $pdo->exec("IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('wr') AND name='{$col}')
                ALTER TABLE wr ADD {$col} VARCHAR(500) NULL");
        } catch (Exception $e) { /* abaikan jika dah wujud */ }
    }

    $uploadDir = __DIR__ . '/../uploads/wr/';
    $updates   = [];
    $params    = [];

    for ($fi = 0; $fi < 3; $fi++) {
        $fn = post('foto_' . $fi, '');
        if (!$fn || !preg_match('/^tmp_[a-zA-Z0-9]+_\d+_\d+\.(jpg|jpeg|png|gif|webp)$/', $fn)) continue;
        $src = __DIR__ . '/../uploads/temp/' . $fn;
        if (!file_exists($src)) continue;

        $ext  = pathinfo($fn, PATHINFO_EXTENSION);
        $col  = 'foto' . ($fi + 1);

        // Cuba MinIO dahulu; jika gagal (down/tiada config), fallback simpan lokal macam biasa
        // supaya submission borang tidak terjejas oleh storan pihak ketiga.
        $minioKey = minioUpload($src, "{$wr_id}/foto" . ($fi + 1) . ".{$ext}");
        if ($minioKey !== null) {
            @unlink($src);
            $updates[] = "{$col} = ?";
            $params[]  = $minioKey;
            continue;
        }

        $dest = 'wr_' . $wr_id . '_' . ($fi + 1) . '.' . $ext;
        if (rename($src, $uploadDir . $dest)) {
            $updates[] = "{$col} = ?";
            $params[]  = $dest;
        }
    }

    if (!empty($updates)) {
        $params[] = $wr_id;
        $pdo->prepare("UPDATE wr SET " . implode(', ', $updates) . " WHERE wr_id = ?")
            ->execute($params);
    }
}

// ---- Parse koordinat ----
$latitude  = '';
$longitude = '';
if ($koordinat_lokasi && strpos($koordinat_lokasi, ',') !== false) {
    [$latitude, $longitude] = explode(',', $koordinat_lokasi, 2);
    $latitude  = trim($latitude);
    $longitude = trim($longitude);
}

// ---- INSERT ----
$wr_id   = null;
$success = false;

try {
    if ($requestor == '1' && $location == '8') {
        // Staf – bilik sendiri
        $sql = "INSERT INTO wr (bl_id,section,prob_type,requestor,em_number,phone,prob_group,type_wr,time_requested,type_req,status,zone_id,state_id,site_id,description,fl_id,rm_id,date_requested,location)
                VALUES (?,?,?,?,?,?,?,?,GETDATE(),?,'R',?,?,?,?,?,?,GETDATE(),?)";
        dbExecute($sql, [
            $bl_id, $section, $prob_type, $requestor_name, $em_number, $phone_val,
            $prob_group, $medium, $type_req, $zone_id, $state_id, $site_id,
            $prob_description, $fl_id, $rm_id, $location
        ]);
        $success = true;

    } elseif ($requestor == '1' && $location == '9') {
        // Staf – lokasi lain
        $sql = "INSERT INTO wr (bl_id,section,prob_type,requestor,em_number,phone,prob_group,type_wr,time_requested,type_req,status,location,zone_id,state_id,site_id,description,date_requested,fl_id,rm_id,infra_id,subinfra_id,latitude,longitude,koordinat_lokasi)
                VALUES (?,?,?,?,?,?,?,?,GETDATE(),?,'R',?,?,?,?,?,GETDATE(),?,?,?,?,?,?,?)";
        dbExecute($sql, [
            $bl_id, $section, $prob_type, $requestor_name, $em_number, $phone_val,
            $prob_group, $medium, $type_req, $location, $zone_id, $state_id, $site_id,
            $prob_description, $fl_id, $rm_id,
            $idkat_infra ?: null, $id_subinfra ?: null,
            $latitude ?: null, $longitude ?: null, $koordinat_lokasi ?: null
        ]);
        $success = true;

    } elseif ($requestor == '2' && $location == '8') {
        $sql = "INSERT INTO wr (bl_id,section,prob_type,requestor,stu_id,phone,prob_group,type_wr,time_requested,type_req,status,zone_id,state_id,site_id,description,fl_id,rm_id,date_requested,location)
                VALUES (?,?,?,?,?,?,?,?,GETDATE(),?,'R',?,?,?,?,?,?,GETDATE(),?)";
        dbExecute($sql, [
            $bl_id, $section, $prob_type, $requestor_name, $stu_id_val, $phone_val,
            $prob_group, $medium, $type_req, $zone_id, $state_id, $site_id,
            $prob_description, $fl_id, $rm_id, $location
        ]);
        $success = true;

    } elseif ($requestor == '2' && $location == '9') {
        $sql = "INSERT INTO wr (bl_id,section,prob_type,requestor,stu_id,phone,prob_group,type_wr,time_requested,type_req,status,location,zone_id,state_id,site_id,description,date_requested,fl_id,rm_id,infra_id,subinfra_id,latitude,longitude,koordinat_lokasi)
                VALUES (?,?,?,?,?,?,?,?,GETDATE(),?,'R',?,?,?,?,?,GETDATE(),?,?,?,?,?,?,?)";
        dbExecute($sql, [
            $bl_id, $section, $prob_type, $requestor_name, $stu_id_val, $phone_val,
            $prob_group, $medium, $type_req, $location, $zone_id, $state_id, $site_id,
            $prob_description, $fl_id, $rm_id,
            $idkat_infra ?: null, $id_subinfra ?: null,
            $latitude ?: null, $longitude ?: null, $koordinat_lokasi ?: null
        ]);
        $success = true;

    } elseif ($requestor == '3' && $location == '8') {
        $sql = "INSERT INTO wr (bl_id,section,prob_type,requestor,hp_requestor,ic_no,phone,prob_group,type_wr,time_requested,type_req,status,zone_id,state_id,site_id,description,fl_id,rm_id,date_requested,location)
                VALUES (?,?,?,?,?,?,?,?,?,GETDATE(),?,'R',?,?,?,?,?,?,GETDATE(),?)";
        dbExecute($sql, [
            $bl_id, $section, $prob_type, $requestor_name, $hp_requestor, $ic_no_val, $phone_val,
            $prob_group, $medium, $type_req, $zone_id, $state_id, $site_id,
            $prob_description, $fl_id, $rm_id, $location
        ]);
        $success = true;

    } elseif ($requestor == '3' && $location == '9') {
        $sql = "INSERT INTO wr (bl_id,section,prob_type,requestor,hp_requestor,ic_no,phone,prob_group,type_wr,time_requested,type_req,status,location,zone_id,state_id,site_id,description,date_requested,fl_id,rm_id,infra_id,subinfra_id,latitude,longitude,koordinat_lokasi)
                VALUES (?,?,?,?,?,?,?,?,?,GETDATE(),?,'R',?,?,?,?,?,GETDATE(),?,?,?,?,?,?,?)";
        dbExecute($sql, [
            $bl_id, $section, $prob_type, $requestor_name, $hp_requestor, $ic_no_val, $phone_val,
            $prob_group, $medium, $type_req, $location, $zone_id, $state_id, $site_id,
            $prob_description, $fl_id, $rm_id,
            $idkat_infra ?: null, $id_subinfra ?: null,
            $latitude ?: null, $longitude ?: null, $koordinat_lokasi ?: null
        ]);
        $success = true;
    }

    if ($success) {
        // Dapatkan wr_id yang betul: query lepas INSERT, filter by requestor + masa
        // (@@IDENTITY/lastInsertId tidak boleh pakai sebab ada trigger pada wr)
        if ($requestor == '1' && $em_number) {
            $r = dbFetchOne(
                "SELECT TOP 1 wr_id FROM wr WHERE em_number = ? AND status = 'R' AND time_requested >= DATEADD(second,-60,GETDATE()) ORDER BY wr_id DESC",
                [$em_number]
            );
        } elseif ($requestor == '2' && $stu_id_val) {
            $r = dbFetchOne(
                "SELECT TOP 1 wr_id FROM wr WHERE stu_id = ? AND status = 'R' AND time_requested >= DATEADD(second,-60,GETDATE()) ORDER BY wr_id DESC",
                [$stu_id_val]
            );
        } elseif ($requestor == '3' && $ic_no_val) {
            $r = dbFetchOne(
                "SELECT TOP 1 wr_id FROM wr WHERE ic_no = ? AND status = 'R' AND time_requested >= DATEADD(second,-60,GETDATE()) ORDER BY wr_id DESC",
                [$ic_no_val]
            );
        }
        $wr_id = $r ? (int)$r['wr_id'] : null;

        if ($wr_id) {
            require_once __DIR__ . '/../lib/email_embom.php';
            email_embom($wr_id, $pdo);
        }
    }
} catch (Exception $ex) {
    $success = false;
    $errorMsg = $ex->getMessage();
}

// Simpan gambar selepas aduan berjaya (kegagalan gambar tidak affect status aduan)
if ($success && $wr_id) {
    try {
        simpanFotoDB($pdo, (int)$wr_id);
    } catch (Exception $ex) {
        error_log('simpanFotoDB gagal wr_id=' . $wr_id . ': ' . $ex->getMessage());
    }
}

$pageTitle = 'Simpan Aduan';
require_once __DIR__ . '/../includes/header.php';
?>

<?php if ($success && $wr_id): ?>
<div class="ea-card">
    <div class="ea-sec-hdr"><i class="bi bi-check-circle"></i> Aduan Berjaya Dihantar</div>
    <div class="card-body p-4 text-center">
        <div class="mb-3">
            <i class="bi bi-patch-check-fill" style="font-size:3rem;color:var(--uitm-purple);"></i>
        </div>
        <h5 class="fw-bold" style="color:var(--uitm-purple-dark);">Terima Kasih!</h5>
        <p class="mb-1">Aduan anda telah berjaya direkodkan.</p>
        <p class="mb-3">
            <strong>No. Aduan:</strong>
            <span class="badge bg-dark fs-6"><?php echo e((string)$wr_id); ?></span>
        </p>
        <p class="text-muted" style="font-size:0.85rem;">Sila simpan nombor aduan ini untuk rujukan semakan status aduan anda.</p>
        <div class="d-flex justify-content-center gap-3 mt-3">
            <a href="<?php echo baseUrl(); ?>/pages/borang.php" class="btn btn-uitm-gold">
                <i class="bi bi-plus-circle"></i> Aduan Baru
            </a>
            <a href="<?php echo baseUrl(); ?>/pages/semak.php" class="btn btn-uitm">
                <i class="bi bi-search"></i> Semak Aduan
            </a>
            <a href="<?php echo baseUrl(); ?>/pages/thanx.php" class="btn btn-outline-secondary">
                <i class="bi bi-house"></i> Selesai
            </a>
        </div>
    </div>
</div>
<?php else: ?>
<div class="ea-card">
    <div class="ea-sec-hdr bg-danger"><i class="bi bi-x-circle"></i> Ralat</div>
    <div class="card-body p-4 text-center">
        <div class="mb-3">
            <i class="bi bi-exclamation-triangle-fill" style="font-size:3rem;color:#dc3545;"></i>
        </div>
        <h5 class="fw-bold text-danger">Aduan Gagal Dihantar</h5>
        <p class="text-muted"><?php echo e($errorMsg ?? 'Sila cuba sekali lagi atau hubungi pentadbir.'); ?></p>
        <a href="<?php echo baseUrl(); ?>/pages/borang.php" class="btn btn-uitm mt-2">
            <i class="bi bi-arrow-left-circle"></i> Kembali ke Borang
        </a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
