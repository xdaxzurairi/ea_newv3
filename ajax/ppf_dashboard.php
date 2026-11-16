<?php
/**
 * eAduan v3 – Portal PPF: data dashboard (JSON, baca-sahaja, semua kampus).
 * GET view, state_id, site_id, zone_id, section, prob_group, date_from, date_to,
 *     wr_id, staff_id, student_id, page, per_page
 */
require_once __DIR__ . '/../includes/ppf_guard.php';

ppfEnforce('json');
header('Content-Type: application/json; charset=utf-8');

const PPF_PER_PAGE  = 10;
const PPF_ATTENTION = 5;

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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    ppfJson(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$view = ppfStr('view', '/^[a-z]{1,12}$/') ?: 'all';
if (!in_array($view, ['all', 'requested', 'completed'], true)) {
    ppfInvalid();
}

$code = '/^[A-Za-z0-9 ._\-\/]{1,40}$/';
$f = [
    'state_id'   => ppfStr('state_id', $code),
    'site_id'    => ppfStr('site_id', $code),
    'zone_id'    => ppfStr('zone_id', $code),
    'section'    => ppfStr('section', $code),
    'prob_group' => ppfStr('prob_group', $code),
];
$wrId      = ppfStr('wr_id', '/^\d{1,9}$/');
$staffId   = ppfStr('staff_id', '/^[A-Za-z0-9._\-]{1,20}$/');
$studentId = ppfStr('student_id', '/^[A-Za-z0-9._\-]{1,20}$/');
$dateFrom  = ppfDate('date_from');
$dateTo    = ppfDate('date_to');
if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
    ppfInvalid();
}
$page = ppfStr('page', '/^\d{1,9}$/');
$page = $page === '' ? 1 : max(1, (int) $page);

$where  = [];
$params = [];
foreach ($f as $col => $val) {
    if ($val !== '') {
        $where[]  = "w.$col = ?";
        $params[] = $val;
    }
}
if ($wrId !== '') {
    $where[]  = 'w.wr_id = ?';
    $params[] = (int) $wrId;
}
if ($staffId !== '') {
    $where[]  = 'w.em_number = ?';
    $params[] = $staffId;
}
if ($studentId !== '') {
    $where[]  = 'w.stu_id = ?';
    $params[] = $studentId;
}
if ($dateFrom !== '') {
    $where[]  = 'w.date_requested >= CAST(? AS date)';
    $params[] = $dateFrom;
}
if ($dateTo !== '') {
    $where[]  = 'w.date_requested < DATEADD(day, 1, CAST(? AS date))';
    $params[] = $dateTo;
}

$statusReq = "UPPER(RTRIM(w.status)) = 'R'";
$statusCom = "UPPER(RTRIM(w.status)) = 'COM'";

$baseWhere = $where ? implode(' AND ', $where) : '1=1';

/** Padan baris wr (sudah dihadkan oleh $inner) dengan nama rujukan; susunan ikut $order. */
function ppfFetchRows(string $inner, array $params, string $order): array
{
    $sql = "SELECT w.wr_id, CONVERT(char(10), w.date_requested, 23) AS date_requested, w.status,
                   DATEDIFF(day, w.date_requested, GETDATE()) AS age_days,
                   w.em_number, w.stu_id, w.ic_no, w.bl_id, w.section, w.prob_group,
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
        $out[] = [
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
    }
    return $out;
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

try {
    $c = dbFetchOne(
        "SELECT COUNT(*) AS c_all,
                SUM(CASE WHEN $statusReq THEN 1 ELSE 0 END) AS c_req,
                SUM(CASE WHEN $statusCom THEN 1 ELSE 0 END) AS c_com
         FROM wr w WHERE $baseWhere",
        $params
    );
    $summary = ['all' => (int) $c['c_all'], 'requested' => (int) $c['c_req'], 'completed' => (int) $c['c_com']];

    $total = $summary[$view];
    $pages = max(1, (int) ceil($total / PPF_PER_PAGE));
    $page  = min($page, $pages);
    $offset = ($page - 1) * PPF_PER_PAGE;

    $cols = 'w.wr_id, w.date_requested, w.status, w.em_number, w.stu_id, w.ic_no, w.state_id, w.site_id, w.bl_id, w.section, w.prob_group';

    $viewWhere = $baseWhere;
    if ($view === 'requested') {
        $viewWhere .= " AND $statusReq";
    } elseif ($view === 'completed') {
        $viewWhere .= " AND $statusCom";
    }
    $complaints = ppfFetchRows(
        "SELECT $cols FROM wr w WHERE $viewWhere ORDER BY w.wr_id DESC OFFSET $offset ROWS FETCH NEXT " . PPF_PER_PAGE . " ROWS ONLY",
        $params,
        'w.wr_id DESC'
    );

    $attention = ppfFetchRows(
        "SELECT TOP " . PPF_ATTENTION . " $cols FROM wr w WHERE $baseWhere AND $statusReq ORDER BY w.date_requested ASC, w.wr_id ASC",
        $params,
        'w.date_requested ASC, w.wr_id ASC'
    );

    $filters = [
        'states'         => ppfOptions("SELECT state_id, name FROM state WHERE eaduan_publish = '1' ORDER BY name"),
        'sites'          => $f['state_id'] !== ''
            ? ppfOptions("SELECT site_id, name FROM site WHERE eaduan_publish = '1' AND state_id = ? ORDER BY name", [$f['state_id']])
            : ppfOptions("SELECT site_id, name FROM site WHERE eaduan_publish = '1' ORDER BY name"),
        'zones'          => $f['site_id'] !== ''
            ? ppfOptions("SELECT zone_id, description FROM zone_uitm WHERE site_id = ? ORDER BY description", [$f['site_id']])
            : [],
        'sections'       => ppfOptions("SELECT section, description FROM section WHERE section <> 'PREVENTIVE MAINT' ORDER BY section"),
        'problem_groups' => $f['section'] !== ''
            ? ppfOptions("SELECT prob_group, description FROM prob_group WHERE section = ? ORDER BY description", [$f['section']])
            : [],
    ];
} catch (Throwable $e) {
    error_log('ppf_dashboard: ' . $e->getMessage());
    ppfJson(500, ['ok' => false, 'error' => 'server_error']);
}

ppfJson(200, [
    'ok'         => true,
    'summary'    => $summary,
    'attention'  => $attention,
    'complaints' => $complaints,
    'pagination' => ['page' => $page, 'per_page' => PPF_PER_PAGE, 'total' => $total, 'pages' => $pages],
    'filters'    => $filters,
    'updated_at' => date('c'),
]);
