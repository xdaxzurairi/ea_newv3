<?php
/**
 * eAduan v3 – Portal PPF: data dashboard (JSON, baca-sahaja, semua kampus).
 * GET view, state_id, site_id, zone_id, section, prob_group, date_from, date_to,
 *     wr_id, staff_id, student_id, page, per_page
 */
require_once __DIR__ . '/../includes/ppf_api.php';

ppfEnforce('json');
header('Content-Type: application/json; charset=utf-8');

const PPF_ATTENTION = 5;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    ppfJson(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$view = ppfStr('view', '/^[a-z]{1,12}$/') ?: 'all';
if (!in_array($view, ['all', 'requested', 'completed'], true)) {
    ppfInvalid();
}

$f         = ppfReadCommonFilters();
$wrId      = ppfStr('wr_id', '/^\d{1,9}$/');
$staffId   = ppfStr('staff_id', '/^[A-Za-z0-9._\-]{1,20}$/');
$studentId = ppfStr('student_id', '/^[A-Za-z0-9._\-]{1,20}$/');
$page      = ppfReadPage();

$params = [];
$where  = ppfCommonWhere($f, $params);
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

$statusReq = "UPPER(RTRIM(w.status)) = 'R'";
$statusCom = "UPPER(RTRIM(w.status)) = 'COM'";

$baseWhere = $where ? implode(' AND ', $where) : '1=1';

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

    $filters = ppfFilterLists($f);
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
