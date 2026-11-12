<?php
/**
 * eAduan v3 – E-mel kepada Pengarah apabila pengguna tidak berpuas hati (work_performance = 2)
 */

function email_pengarah($noaduan, $pdo)
{
    $wr = dbFetchOne(
        "SELECT section, prob_group, prob_type, site_id, zone_id, state_id, bl_id,
                FORMAT(date_requested, 'dd-MM-yyyy') AS date_requested,
                requestor, description, satisfaction_notes, work_performance
         FROM wr WHERE wr_id = ?",
        [$noaduan]
    );
    if (!$wr || trim($wr['work_performance'] ?? '') !== '2') {
        return;
    }

    $site_id    = trim($wr['site_id'] ?? '');
    $state_id   = trim($wr['state_id'] ?? '');
    $section    = trim($wr['section'] ?? '');
    $prob_group = trim($wr['prob_group'] ?? '');
    $prob_type  = trim($wr['prob_type'] ?? '');
    $date_req   = trim($wr['date_requested'] ?? '');
    $requestor  = trim($wr['requestor'] ?? '');
    $wr_desc    = trim($wr['description'] ?? '');
    $notes      = trim($wr['satisfaction_notes'] ?? '');

    $lokasi = trim($wr['bl_id'] ?? '');
    if ($lokasi) {
        $bl = dbFetchOne("SELECT name FROM bl WHERE bl_id = ?", [$lokasi]);
        if ($bl) {
            $lokasi = trim($bl['name']);
        }
    }

    $prob_desc = '';
    $pt = dbFetchOne(
        "SELECT t.description AS t, g.description AS g
         FROM probtype t, prob_group g
         WHERE t.prob_type = ? AND t.section = ? AND g.prob_group = ? AND g.section = t.section",
        [$prob_type, $section, $prob_group]
    );
    if ($pt) {
        $prob_desc = descSlashToDash(trim($pt['g']) . ' / ' . trim($pt['t']));
    }

    $to_emails = [];
    $pengarah_rows = dbFetchAll(
        "SELECT e.email, e.em_id
         FROM em e
         INNER JOIN em_pengarah p ON p.em_number = e.em_number
         WHERE p.site_id = ?
           AND (p.state_id = ? OR p.state_id IS NULL OR LTRIM(RTRIM(p.state_id)) = '')",
        [$site_id, $state_id]
    );
    foreach ($pengarah_rows as $row) {
        $email = trim($row['email'] ?? '');
        if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $to_emails[$email] = true;
        }
    }

    if (empty($to_emails)) {
        error_log('email_pengarah: tiada e-mel Pengarah untuk wr_id=' . $noaduan . ' site_id=' . $site_id);
        return;
    }

    $cfg = getAuthConfig();
    $base = rtrim($cfg['base_url'] ?? 'https://fmsdev.uitm.edu.my/ea_newv3', '/');

    $subject = "e-Aduan Fasiliti: Maklumbalas Tidak Memuaskan – Aduan #{$noaduan}";

    $body  = "<html><body style='font-family:Arial,sans-serif;font-size:14px;'>";
    $body .= "<h3 style='color:#5B2D8E;'>e-Aduan – Maklumbalas Tidak Memuaskan</h3>";
    $body .= "<p>Assalamualaikum w.b.t dan selamat sejahtera,</p>";
    $body .= "<p>Pengguna melaporkan <strong>tidak berpuas hati</strong> terhadap kerja pembaikan aduan berikut:</p>";
    $body .= "<table style='border-collapse:collapse;width:100%;max-width:600px;'>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>No. Aduan</td><td style='padding:6px 12px;'>{$noaduan}</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Tarikh</td><td style='padding:6px 12px;'>{$date_req}</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Pelapor</td><td style='padding:6px 12px;'>" . htmlspecialchars($requestor ?: '-', ENT_QUOTES, 'UTF-8') . "</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Lokasi</td><td style='padding:6px 12px;'>" . htmlspecialchars($lokasi, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Jenis Kerosakan</td><td style='padding:6px 12px;'>" . htmlspecialchars($prob_desc, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    if ($wr_desc !== '') {
        $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Keterangan</td><td style='padding:6px 12px;'>" . nl2br(htmlspecialchars($wr_desc, ENT_QUOTES, 'UTF-8')) . "</td></tr>";
    }
    if ($notes !== '') {
        $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Catatan Pengguna</td><td style='padding:6px 12px;'>" . nl2br(htmlspecialchars($notes, ENT_QUOTES, 'UTF-8')) . "</td></tr>";
    }
    $body .= "<tr><td style='padding:6px 12px;font-weight:bold;background:#f5f3f7;'>Tindakan</td><td style='padding:6px 12px;'>Sila log masuk ke <a href='{$base}'>Sistem e-Aduan</a> untuk semakan lanjut.</td></tr>";
    $body .= "</table>";
    $body .= "<hr style='border:none;border-top:2px solid #D4A843;'>";
    $body .= "<p style='font-size:12px;color:#888;'>E-mel ini dijana secara automatik oleh Sistem e-Aduan v3, UiTM.</p>";
    $body .= "</body></html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: eAduan <jpem@uitm.edu.my>\r\n";

    foreach (array_keys($to_emails) as $to) {
        @mail($to, $subject, $body, $headers);
    }
}
