<?php
/**
 * eAduan v3 – Portal PPF: fungsi kongsi untuk endpoint JSON (validasi penapis, WHERE, pemetaan baris, senarai penapis).
 * Dipanggil selepas ppfEnforce('json').
 */
require_once __DIR__ . '/ppf_guard.php';

const PPF_PER_PAGE = 10;

function ppfJson(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function ppfInvalid(): void
{
    ppfJson(400, ['ok' => false, 'error' => 'invalid_filters']);
}

function ppfStr(string $key, string $pattern): string
{
    $v = $_GET[$key] ?? '';
    if (!is_string($v)) {
        ppfInvalid();
    }
    $v = trim($v);
    if ($v !== '' && !preg_match($pattern, $v)) {
        ppfInvalid();
    }
    return $v;
}

function ppfDate(string $key): string
{
    $v = ppfStr($key, '/^\d{4}-\d{2}-\d{2}$/');
    if ($v === '') {
        return '';
    }
    $d = DateTime::createFromFormat('!Y-m-d', $v);
    $err = DateTime::getLastErrors();
    if (!$d || ($err && ($err['warning_count'] || $err['error_count'])) || $d->format('Y-m-d') !== $v) {
        ppfInvalid();
    }
    return $v;
}

function ppfLabel(?string $s): string
{
    return descSlashToDash($s);
}

/** Baca penapis lokasi/jenis/tarikh yang sama untuk semua endpoint PPF. */
function ppfReadCommonFilters(): array
{
    $code = '/^[A-Za-z0-9 ._\-\/]{1,40}$/';
    $f = [
        'state_id'   => ppfStr('state_id', $code),
        'site_id'    => ppfStr('site_id', $code),
        'zone_id'    => ppfStr('zone_id', $code),
        'section'    => ppfStr('section', $code),
        'prob_group' => ppfStr('prob_group', $code),
        'date_from'  => ppfDate('date_from'),
        'date_to'    => ppfDate('date_to'),
    ];
    if ($f['date_from'] !== '' && $f['date_to'] !== '' && $f['date_from'] > $f['date_to']) {
        ppfInvalid();
    }
    return $f;
}

function ppfReadPage(): int
{
    $page = ppfStr('page', '/^\d{1,9}$/');
    return $page === '' ? 1 : max(1, (int) $page);
}

/** @return string[] klausa WHERE (alias wr = w); $params diisi mengikut turutan. */
function ppfCommonWhere(array $f, array &$params): array
{
    $where = ["UPPER(RTRIM(ISNULL(w.section, ''))) <> ?"];
    $params[] = PPF_EXCLUDED_SECTION;
    foreach (['state_id', 'site_id', 'zone_id', 'section', 'prob_group'] as $col) {
        if ($f[$col] !== '') {
            $where[]  = "w.$col = ?";
            $params[] = $f[$col];
        }
    }
    if ($f['date_from'] !== '') {
        $where[]  = 'w.date_requested >= CAST(? AS date)';
        $params[] = $f['date_from'];
    }
    if ($f['date_to'] !== '') {
        $where[]  = 'w.date_requested < DATEADD(day, 1, CAST(? AS date))';
        $params[] = $f['date_to'];
    }
    return $where;
}

/**
 * Padan baris wr (sudah dihadkan oleh $inner) dengan nama rujukan. $inner mesti memilih lajur wr
 * w.wr_id, date_requested, status, em_number, stu_id, ic_no, state_id, site_id, bl_id, section, prob_group
 * (dan work_performance, satisfaction_notes, satisfaction_date jika $withFeedback).
 */
function ppfFetchRows(string $inner, array $params, string $order, bool $withFeedback = false): array
{
    $fbCols = $withFeedback ? ', w.work_performance, w.satisfaction_notes, w.satisfaction_date' : '';
    $sql = "SELECT w.wr_id, CONVERT(char(10), w.date_requested, 23) AS date_requested, w.status,
                   DATEDIFF(day, w.date_requested, GETDATE()) AS age_days,
                   w.em_number, w.stu_id, w.ic_no, w.bl_id, w.section, w.prob_group $fbCols,
                   st.name AS state_name, si.name AS site_name, bl.name AS bl_name,
                   sc.description AS sec_desc, pg.description AS pg_desc,
                   COALESCE(em.em_id COLLATE DATABASE_DEFAULT, stu.stu_name COLLATE DATABASE_DEFAULT, osd.name_user COLLATE DATABASE_DEFAULT) AS requestor
            FROM ($inner) w
            LEFT JOIN state st ON st.state_id = w.state_id
            LEFT JOIN site si ON si.state_id = w.state_id AND si.site_id = w.site_id
            LEFT JOIN bl ON bl.bl_id = w.bl_id
            LEFT JOIN section sc ON sc.section = w.section
            LEFT JOIN prob_group pg ON pg.section = w.section AND pg.prob_group = w.prob_group
            LEFT JOIN em ON em.em_number = w.em_number
            LEFT JOIN stu ON stu.stu_id = w.stu_id
            LEFT JOIN afm_user_osd osd ON osd.ic_no COLLATE DATABASE_DEFAULT = w.ic_no COLLATE DATABASE_DEFAULT
            ORDER BY $order";
    $out = [];
    foreach (dbFetchAll($sql, $params) as $r) {
        $st   = strtoupper(trim((string) $r['status']));
        $info = getWrStatusInfo($st);
        $label = $st === 'R' ? 'Baharu' : ($st === 'COM' ? 'Siap' : $info['label']);
        $reqId = trim((string) ($r['em_number'] ?? '')) ?: trim((string) ($r['stu_id'] ?? '')) ?: trim((string) ($r['ic_no'] ?? ''));
        $blId  = trim((string) ($r['bl_id'] ?? ''));
        $row = [
            'wr_id'               => (int) $r['wr_id'],
            'date_requested'      => trim((string) $r['date_requested']),
            'requestor'           => trim((string) ($r['requestor'] ?? '')),
            'requestor_id'        => $reqId,
            'state_name'          => trim((string) ($r['state_name'] ?? '')),
            'site_name'           => trim((string) ($r['site_name'] ?? '')),
            'location_label'      => trim((string) ($r['bl_name'] ?? '')) ?: $blId,
            'section_label'       => ppfLabel($r['sec_desc'] ?? '') ?: trim((string) $r['section']),
            'problem_group_label' => ppfLabel($r['pg_desc'] ?? '') ?: trim((string) $r['prob_group']),
            'status_code'         => $st,
            'status_label'        => $label,
            'age_days'            => max(0, (int) $r['age_days']),
        ];
        if ($withFeedback) {
            $code = ppfFeedbackCode($r['work_performance']);
            $row['feedback_code']  = $code;
            $row['feedback_label'] = ppfFeedbackLabels()[$code];
            $row['feedback_notes'] = trim((string) ($r['satisfaction_notes'] ?? ''));
            $row['feedback_date']  = $r['satisfaction_date'] ? substr((string) $r['satisfaction_date'], 0, 10) : '';
        }
        $out[] = $row;
    }
    return $out;
}

function ppfFeedbackLabels(): array
{
    return ['memuaskan' => 'Memuaskan', 'tidak_memuaskan' => 'Tidak memuaskan', 'tiada' => 'Tiada maklum balas'];
}

/** work_performance: 1 = memuaskan, 2 = tidak memuaskan; NULL atau 0 (belum dinilai) = tiada. */
function ppfFeedbackCode($wp): string
{
    $v = $wp === null ? 0 : (int) $wp;
    return $v === 1 ? 'memuaskan' : ($v === 2 ? 'tidak_memuaskan' : 'tiada');
}

function ppfOptions(string $sql, array $params = []): array
{
    $out = [];
    foreach (dbFetchAll($sql, $params) as $r) {
        $vals = array_values($r);
        $out[] = ['value' => trim((string) $vals[0]), 'label' => ppfLabel((string) $vals[1]) ?: trim((string) $vals[0])];
    }
    return $out;
}

/** Senarai pilihan penapis; kampus ikut negeri, zon ikut kampus, elemen ikut seksyen. */
function ppfFilterLists(array $f): array
{
    return [
        'states'         => ppfOptions("SELECT state_id, name FROM state WHERE eaduan_publish = '1' ORDER BY name"),
        'sites'          => $f['state_id'] !== ''
            ? ppfOptions("SELECT site_id, name FROM site WHERE eaduan_publish = '1' AND state_id = ? ORDER BY name", [$f['state_id']])
            : ppfOptions("SELECT site_id, name FROM site WHERE eaduan_publish = '1' ORDER BY name"),
        'zones'          => $f['site_id'] !== ''
            ? ppfOptions("SELECT zone_id, description FROM zone_uitm WHERE site_id = ? ORDER BY description", [$f['site_id']])
            : [],
        'sections'       => ppfOptions("SELECT section, description FROM section WHERE section <> 'PREVENTIVE MAINT' AND UPPER(RTRIM(section)) <> ? ORDER BY section", [PPF_EXCLUDED_SECTION]),
        'problem_groups' => $f['section'] !== ''
            ? ppfOptions("SELECT prob_group, description FROM prob_group WHERE section = ? ORDER BY description", [$f['section']])
            : [],
    ];
}
