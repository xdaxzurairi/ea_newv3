<?php
/**
 * eAduan v3 – Hantar E-mel kepada BOM (Building Operation Manager)
 * Dipanggil selepas INSERT aduan berjaya.
 * Format ikut v3; maklumat tambahan ikut email lama (salam, BOM, maklumbalas, nota 3 hari).
 */

function email_embom($noaduan, $pdo)
{
    $wr = dbFetchOne(
        "SELECT section,prob_group,site_id,zone_id,bl_id,FORMAT(date_requested,'dd-MM-yyyy')date_requested,type_req,em_number,ic_no,stu_id,prob_type,requestor,type_wr,description FROM wr WHERE wr_id=?",
        [$noaduan]
    );
    if (!$wr) return;

    $section    = trim($wr['section'] ?? '');
    $prob_group = trim($wr['prob_group'] ?? '');
    $site_id    = trim($wr['site_id'] ?? '');
    $zone_id    = trim($wr['zone_id'] ?? '');
    $bl_id      = trim($wr['bl_id'] ?? '');
    $date_req   = trim($wr['date_requested'] ?? '');
    $type_req   = trim($wr['type_req'] ?? '');
    $em_number  = trim($wr['em_number'] ?? '');
    $ic_no      = trim($wr['ic_no'] ?? '');
    $stu_id     = trim($wr['stu_id'] ?? '');
    $prob_type  = trim($wr['prob_type'] ?? '');
    $requestor_name = trim($wr['requestor'] ?? '');
    $type_wr    = trim($wr['type_wr'] ?? '1');
    $wr_desc    = trim($wr['description'] ?? '');

    // Lokasi (nama bangunan)
    $lokasi = $bl_id;
    if ($bl_id) {
        $bl = dbFetchOne("SELECT name FROM bl WHERE bl_id=?", [$bl_id]);
        if ($bl) $lokasi = trim($bl['name']);
    }

    $mediumLabels = function_exists('getMediumLabels') ? getMediumLabels() : ['1'=>'Online','2'=>'Surat','3'=>'Telefon','4'=>'Inisiatif PPF','5'=>'Borang ADKRS','6'=>'Memo','7'=>'E-mail','8'=>'Minit Mesyuarat'];
    $medium_text  = $mediumLabels[$type_wr] ?? $type_wr;

    // Requestor email
    $requestor_email = '';
    if ($em_number) {
        $r = dbFetchOne("SELECT email FROM em WHERE em_number=?", [$em_number]);
        if ($r) $requestor_email = trim($r['email']);
    } elseif ($stu_id) {
        $r = dbFetchOne("SELECT email FROM stu WHERE stu_id=?", [$stu_id]);
        if ($r) $requestor_email = trim($r['email']);
    } elseif ($ic_no) {
        $r = dbFetchOne("SELECT email FROM afm_user_osd WHERE ic_no=?", [$ic_no]);
        if ($r) $requestor_email = trim($r['email']);
    }

    // Probtype description
    $prob_desc = '';
    $pt = dbFetchOne(
        "SELECT t.description as t, g.description as g FROM probtype t, prob_group g WHERE t.prob_type=? AND t.section=? AND g.prob_group=?",
        [$prob_type, $section, $prob_group]
    );
    if ($pt) {
        $prob_desc = descSlashToDash(trim($pt['g']) . ' / ' . trim($pt['t']));
    }

    // BOM list (nama & telefon untuk maklumat "dimajukan kepada")
    $bom_list = dbFetchAll(
        "SELECT b.em_number,email,em_id,phone FROM em e LEFT JOIN em_bom b ON b.em_number=e.em_number WHERE site_id=? AND (zone_id=? OR zone_id IS NULL) AND (section=? OR section IS NULL) AND (prob_group=? OR prob_group IS NULL) AND (bldg_id=? OR bldg_id IS NULL)",
        [$site_id, $zone_id, $section, $prob_group, $bl_id]
    );

    $to_emails = [];
    $bom_names = [];
    $bom_phones = [];
    $bom_dimajukan_lines = [];
    foreach ($bom_list as $bom) {
        $email = trim($bom['email'] ?? '');
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $to_emails[] = $email;
        }
        $em_id = trim($bom['em_id'] ?? '');
        if ($em_id) $bom_names[] = $em_id;
        $ph = trim($bom['phone'] ?? '');
        if ($ph) $bom_phones[] = $ph;
        if ($em_id !== '' || $ph !== '') {
            $line = ($em_id !== '' ? 'En/Pn. ' . $em_id : '') . ($ph !== '' ? ($em_id !== '' ? ' – ' : '') . $ph : '');
            $bom_dimajukan_lines[] = $line;
        }
    }
    $bom_names_str  = count($bom_names) ? 'En/Pn. ' . implode(', ', $bom_names) : '';
    $bom_phones_str = count($bom_phones) ? implode(', ', $bom_phones) : '-';
    $bom_dimajukan_esc = count($bom_dimajukan_lines)
        ? implode("<br>", array_map(function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }, $bom_dimajukan_lines))
        : '-';

    if (empty($to_emails) && empty($requestor_email)) {
        return;
    }

    $type_req_label = ($type_req == '2' ? 'Dalaman (CF)' : ($type_req == '5' ? 'Inisiatif PPF' : 'Luaran'));

    $subject = "e-Aduan Fasiliti: Aduan Baru #{$noaduan} – {$prob_desc}";

    // ----- E-mel kepada BOM (susunan maklumat ikut skrg) -----
    $body  = "<html><body style='font-family:Arial,sans-serif;font-size:14px;'>";
    $body .= "<h3 style='color:#5B2D8E;'>e-Aduan – Pemberitahuan Aduan Fasiliti</h3>";
    $body .= "<table style='border-collapse:collapse;width:100%;max-width:600px;'>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>No. Aduan</td><td style='padding:6px 12px;'>{$noaduan}</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Tarikh</td><td style='padding:6px 12px;'>{$date_req}</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Pelapor</td><td style='padding:6px 12px;'>" . htmlspecialchars($requestor_name ?: '-') . "</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Lokasi</td><td style='padding:6px 12px;'>" . htmlspecialchars($lokasi) . "</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Jenis Kerosakan</td><td style='padding:6px 12px;'>{$prob_desc}</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Medium</td><td style='padding:6px 12px;'>" . htmlspecialchars($medium_text) . "</td></tr>";
    if ($wr_desc !== '') {
        $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Keterangan</td><td style='padding:6px 12px;'>" . nl2br(htmlspecialchars($wr_desc)) . "</td></tr>";
    }
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Jenis Permintaan</td><td style='padding:6px 12px;'>{$type_req_label}</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Tindakan</td><td style='padding:6px 12px;'>Sila log masuk ke <a href='https://eaduan.uitm.edu.my'>Sistem e-Aduan</a> untuk tindakan selanjutnya.</td></tr>";
    $body .= "</table>";
    $body .= "<hr style='border:none;border-top:2px solid #D4A843;'>";
    $body .= "<p style='font-size:12px;color:#888;'>E-mel ini dijana secara automatik oleh Sistem e-Aduan, UiTM.</p>";
    $body .= "</body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: eAduan <eaduan@uitm.edu.my>\r\n";

    foreach ($to_emails as $to) {
        @mail($to, $subject, $body, $headers);
    }

    // ----- E-mel kepada pengadu (semua maklumat dalam jadual) -----
    if ($requestor_email && filter_var($requestor_email, FILTER_VALIDATE_EMAIL)) {
        $subjectReq = "Maklumat aduan anda";
        $bodyReq  = "<html><body style='font-family:Arial,sans-serif;font-size:14px;'>";
        $bodyReq .= "<p>Assalamualaikum w.b.t dan selamat sejahtera,</p>";
        $bodyReq .= "<p>Aduan anda telah berjaya diterima. Maklumat seperti di bawah:</p>";
        $bodyReq .= "<table style='border-collapse:collapse;width:100%;max-width:600px;'>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>No. Aduan</td><td style='padding:6px 12px;'>{$noaduan}</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Tarikh</td><td style='padding:6px 12px;'>{$date_req}</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Jenis Kerosakan</td><td style='padding:6px 12px;'>" . htmlspecialchars($prob_desc, ENT_QUOTES, 'UTF-8') . "</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Medium</td><td style='padding:6px 12px;'>" . htmlspecialchars($medium_text, ENT_QUOTES, 'UTF-8') . "</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Dimajukan kepada (nama – no. tel)</td><td style='padding:6px 12px;'>{$bom_dimajukan_esc}</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Semak status aduan</td><td style='padding:6px 12px;'><a href='https://eaduan.uitm.edu.my'>Sistem e-Aduan</a></td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Maklumbalas pelanggan</td><td style='padding:6px 12px;'>Boleh diisi di laman web tersebut apabila kerja-kerja pembaikan telah disiapkan.</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Nota penilaian</td><td style='padding:6px 12px;'>Pengguna dianggap berpuas hati sekiranya tidak membuat penilaian dalam tempoh tiga (3) hari setelah kerja siap.</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Catatan</td><td style='padding:6px 12px;'>Sekian dan terima kasih di atas aduan anda.</td></tr>";
        $bodyReq .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Sistem</td><td style='padding:6px 12px;font-size:12px;color:#888;'>E-mel ini dijana secara automatik oleh Sistem e-Aduan v3, UiTM.</td></tr>";
        $bodyReq .= "</table>";
        $bodyReq .= "<hr style='border:none;border-top:2px solid #D4A843;'>";
        $bodyReq .= "</body></html>";
        @mail($requestor_email, $subjectReq, $bodyReq, $headers);
    }
}
