<?php
/**
 * Popup Pilih Bilik Sendiri – eAduan v3
 *
 * Cater bilik sendiri apabila user mempunyai lebih dari satu bilik (selari dengan
 * eAduan/staf_UiTM/bilik_sendiri.php):
 * - Staf (requestor=1): Semua baris dari em WHERE em_number=? (bl_id, fl_id, rm_id)
 * - Pelajar (requestor=2): Semua baris dari rm WHERE stud_id=?
 * Bila user klik satu baris, pilihBilik() isi form induk (neg, kamp, bang, blk, fl, ruang
 * dan label idneg, idkamp, idbang, idblk, idfl, idruang) lalu submit.
 */
require_once __DIR__ . '/../includes/functions.php';

$no_em     = trim($_GET['txt_emnumber'] ?? '');
$no_stud   = trim($_GET['no_stud'] ?? '');
$requestor = trim($_GET['requestor'] ?? '');
$location  = trim($_GET['location'] ?? '');

$rooms = [];
$no_room_data = false;

if ($requestor === '1' && $no_em !== '') {
    $rs = dbFetchAll("SELECT bl_id, fl_id, rm_id FROM em WHERE em_number = ?", [$no_em]);
    if (empty($rs)) {
        $no_room_data = true;
    } else {
        foreach ($rs as $r) {
            $s_bl = trim($r['bl_id']);
            $s_fl = trim($r['fl_id']);
            $s_rm = trim($r['rm_id']);
            if ($s_bl === '' || $s_fl === '' || $s_rm === '') continue;

            $bl_row    = dbFetchOne("SELECT bl_id, name, state_id, site_id, bldg_id FROM bl WHERE bl_id = ?", [$s_bl]);
            if (!$bl_row) continue;

            $state_row = dbFetchOne("SELECT state_id, name FROM state WHERE state_id = ?", [trim($bl_row['state_id'])]);
            $site_row  = dbFetchOne("SELECT site_id, name FROM site WHERE site_id = ?", [trim($bl_row['site_id'])]);
            $bldg_row  = dbFetchOne("SELECT bldg_id, bl_name FROM bldg WHERE bldg_id = ?", [trim($bl_row['bldg_id'])]);
            $fl_row    = dbFetchOne("SELECT fl_id, name FROM fl WHERE fl_id = ?", [$s_fl]);
            $rm_row    = dbFetchOne("SELECT rm_id, bl_id, fl_id, name FROM rm WHERE rm_id = ? AND bl_id = ? AND fl_id = ?", [$s_rm, $s_bl, $s_fl]);

            $rooms[] = [
                'kod'       => $s_bl . $s_fl . $s_rm,
                'bl_id'     => $s_bl,
                'fl_id'     => $s_fl,
                'rm_id'     => $s_rm,
                'namebl'    => $bl_row ? trim($bl_row['name']) : '',
                'state_id'  => $state_row ? trim($state_row['state_id']) : '',
                'namest'    => $state_row ? trim($state_row['name']) : '',
                'site_id'   => $site_row ? trim($site_row['site_id']) : '',
                'namesite'  => $site_row ? trim($site_row['name']) : '',
                'bldg_id'   => $bldg_row ? trim($bldg_row['bldg_id']) : '',
                'builname'  => $bldg_row ? trim($bldg_row['bl_name']) : '',
                'namefl'    => $fl_row ? trim($fl_row['name']) : '',
                'namerm'    => $rm_row ? trim($rm_row['name']) : '',
            ];
        }
        if (empty($rooms)) $no_room_data = true;
    }
} elseif ($requestor === '2' && $no_stud !== '') {
    $rs = dbFetchAll("SELECT rm_id, bl_id, fl_id FROM rm WHERE stud_id = ?", [$no_stud]);
    if (empty($rs)) {
        $no_room_data = true;
    } else {
        foreach ($rs as $r) {
            $s_bl = trim($r['bl_id']);
            $s_fl = trim($r['fl_id']);
            $s_rm = trim($r['rm_id']);

            $bl_row    = dbFetchOne("SELECT bl_id, name, state_id, site_id, bldg_id FROM bl WHERE bl_id = ?", [$s_bl]);
            if (!$bl_row) continue;

            $state_row = dbFetchOne("SELECT state_id, name FROM state WHERE state_id = ?", [trim($bl_row['state_id'])]);
            $site_row  = dbFetchOne("SELECT site_id, name FROM site WHERE site_id = ?", [trim($bl_row['site_id'])]);
            $bldg_row  = dbFetchOne("SELECT bldg_id, bl_name FROM bldg WHERE bldg_id = ?", [trim($bl_row['bldg_id'])]);
            $fl_row    = dbFetchOne("SELECT fl_id, name FROM fl WHERE fl_id = ?", [$s_fl]);
            $rm_row    = dbFetchOne("SELECT rm_id, bl_id, fl_id, name FROM rm WHERE rm_id = ? AND bl_id = ? AND fl_id = ?", [$s_rm, $s_bl, $s_fl]);

            $rooms[] = [
                'kod'       => $s_bl . $s_fl . $s_rm,
                'bl_id'     => $s_bl,
                'fl_id'     => $s_fl,
                'rm_id'     => $s_rm,
                'namebl'    => $bl_row ? trim($bl_row['name']) : '',
                'state_id'  => $state_row ? trim($state_row['state_id']) : '',
                'namest'    => $state_row ? trim($state_row['name']) : '',
                'site_id'   => $site_row ? trim($site_row['site_id']) : '',
                'namesite'  => $site_row ? trim($site_row['name']) : '',
                'bldg_id'   => $bldg_row ? trim($bldg_row['bldg_id']) : '',
                'builname'  => $bldg_row ? trim($bldg_row['bl_name']) : '',
                'namefl'    => $fl_row ? trim($fl_row['name']) : '',
                'namerm'    => $rm_row ? trim($rm_row['name']) : '',
            ];
        }
        if (empty($rooms)) $no_room_data = true;
    }
} else {
    $no_room_data = true;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Bilik Sendiri – e-Aduan v3</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root {
        --uitm-purple: #5B2D8E;
        --uitm-purple-dark: #3D1A6E;
        --uitm-gold: #D4A843;
        --uitm-gold-light: #F5E6C0;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Inter', -apple-system, sans-serif;
        background: #F5F3F7;
        padding: 16px;
        font-size: 0.85rem;
        color: #212529;
    }
    .popup-hdr {
        background: linear-gradient(135deg, var(--uitm-purple), var(--uitm-purple-dark));
        color: #fff;
        padding: 12px 18px;
        border-radius: 10px 10px 0 0;
        font-weight: 700;
        font-size: 0.92rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .popup-gold { height: 3px; background: linear-gradient(90deg, var(--uitm-gold), var(--uitm-gold-light), var(--uitm-gold)); }
    .popup-body {
        background: #fff;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .popup-table {
        width: 100%;
        border-collapse: collapse;
    }
    .popup-table thead th {
        background: #F8F5FB;
        color: var(--uitm-purple-dark);
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 10px 12px;
        text-align: left;
        border-bottom: 2px solid var(--uitm-gold-light);
        white-space: nowrap;
    }
    .popup-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.84rem;
        vertical-align: middle;
    }
    .popup-table tbody tr { cursor: pointer; transition: background 0.15s; }
    .popup-table tbody tr:hover { background: #F5F0FA; }
    .popup-table tbody tr:active { background: #EDE5F5; }
    .room-link {
        color: var(--uitm-purple);
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .room-link:hover { color: var(--uitm-gold); }
    .room-link .bi { font-size: 0.7rem; }
    .no-data {
        text-align: center;
        padding: 32px 16px;
        color: #999;
    }
    .no-data i { font-size: 2rem; display: block; margin-bottom: 8px; color: #ddd; }
    @media (max-width: 600px) {
        body { padding: 10px; }
        .popup-table thead { display: none; }
        .popup-table tbody tr {
            display: block;
            margin-bottom: 8px;
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 8px 10px;
            background: #fff;
        }
        .popup-table tbody td {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border: none;
            font-size: 0.82rem;
        }
        .popup-table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--uitm-purple-dark);
            font-size: 0.72rem;
            text-transform: uppercase;
            flex-shrink: 0;
            margin-right: 8px;
        }
    }
    </style>

<?php if ($no_room_data): ?>
    <script>
    (function(){
        alert("Ruang bilik sendiri tidak/belum didaftarkan.\nSila kemaskini maklumat ruang di portal FMS Aplikasi e-Ruang.");
        var url = "https://fms.uitm.edu.my/eruang/ruang_staff2/";
        if (window.opener) { window.opener.location.href = url; window.close(); }
        else { window.location.href = url; }
    })();
    </script>
<?php endif; ?>

    <script>
    function pilihBilik(data) {
        var form = window.opener.document.form1;
        form.neg.value       = data.state_id;
        form.idneg.value     = data.namest;
        form.kamp.value      = data.site_id;
        form.idkamp.value    = data.namesite;
        form.bang.value       = data.bldg_id;
        form.idbang.value    = data.builname;
        form.blk.value       = data.bl_id;
        form.idblk.value     = data.namebl;
        form.fl.value        = data.fl_id;
        form.idfl.value      = data.namefl;
        form.ruang.value     = data.rm_id;
        form.idruang.value   = data.namerm;

        form.submit();
        window.close();
    }
    </script>
</head>
<body>

<div class="popup-hdr"><i class="bi bi-door-open-fill"></i> Pilih Bilik Sendiri</div>
<div class="popup-gold"></div>
<div class="popup-body">

<?php if (!empty($rooms)): ?>
    <div style="overflow-x:auto;">
    <table class="popup-table">
        <thead>
        <tr>
            <th>No. Ruang</th>
            <th>Bangunan</th>
            <th>Blok</th>
            <th>Aras</th>
            <th>No. Bilik</th>
            <th>Nama Bilik</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rooms as $rm): ?>
        <tr onclick='pilihBilik(<?php echo json_encode($rm, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
            <td data-label="No. Ruang">
                <span class="room-link"><i class="bi bi-arrow-right-circle-fill"></i> <?php echo e($rm['kod']); ?></span>
            </td>
            <td data-label="Bangunan"><?php echo e($rm['builname']); ?></td>
            <td data-label="Blok"><?php echo e($rm['namebl']); ?></td>
            <td data-label="Aras"><?php echo e($rm['fl_id'] . ' - ' . $rm['namefl']); ?></td>
            <td data-label="No. Bilik"><?php echo e($rm['rm_id']); ?></td>
            <td data-label="Nama Bilik"><?php echo e($rm['namerm']); ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
<?php else: ?>
    <div class="no-data">
        <i class="bi bi-inbox"></i>
        Tiada data bilik dijumpai.
    </div>
<?php endif; ?>

</div>
</body>
</html>
