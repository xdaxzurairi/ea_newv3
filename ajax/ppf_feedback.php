<?php
/**
 * eAduan v3 – Portal PPF: senarai maklum balas pengguna (JSON, baca-sahaja, semua kampus).
 * Skop: aduan berstatus Com atau I (seperti portal lama).
 * GET feedback=all|ada|memuaskan|tidak_memuaskan|tiada, state_id, site_id, zone_id, section, prob_group,
 *     date_from, date_to, wr_id, page
 */
require_once __DIR__ . '/../includes/ppf_api.php';

ppfEnforce('json');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    ppfJson(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$feedback = ppfStr('feedback', '/^[a-z_]{1,20}$/') ?: 'all';
if (!in_array($feedback, ['all', 'ada', 'memuaskan', 'tidak_memuaskan', 'tiada'], true)) {
    ppfInvalid();
}

$f    = ppfReadCommonFilters();
$wrId = ppfStr('wr_id', '/^\d{1,9}$/');
$page = ppfReadPage();

$params = [];
$where  = ppfCommonWhere($f, $params);
if ($wrId !== '') {
    $where[]  = 'w.wr_id = ?';
    $params[] = (int) $wrId;
}
$where[] = "UPPER(RTRIM(w.status)) IN ('COM', 'I')";
$baseWhere = implode(' AND ', $where);

$fbWhere = [
    'memuaskan'       => 'w.work_performance = 1',
    'tidak_memuaskan' => 'w.work_performance = 2',
    'ada'             => 'w.work_performance IN (1, 2)',
    'tiada'           => '(w.work_performance IS NULL OR w.work_performance NOT IN (1, 2))',
];

try {
    $c = dbFetchOne(
        "SELECT COUNT(*) AS c_all,
                SUM(CASE WHEN w.work_performance = 1 THEN 1 ELSE 0 END) AS c_ok,
                SUM(CASE WHEN w.work_performance = 2 THEN 1 ELSE 0 END) AS c_bad
         FROM wr w WHERE $baseWhere",
        $params
    );
    $ok  = (int) $c['c_ok'];
    $bad = (int) $c['c_bad'];
    $all = (int) $c['c_all'];
    $summary = ['all' => $all, 'ada' => $ok + $bad, 'memuaskan' => $ok, 'tidak_memuaskan' => $bad, 'tiada' => $all - $ok - $bad];

    $total  = $summary[$feedback];
    $pages  = max(1, (int) ceil($total / PPF_PER_PAGE));
    $page   = min($page, $pages);
    $offset = ($page - 1) * PPF_PER_PAGE;

    $viewWhere = $baseWhere . (isset($fbWhere[$feedback]) ? ' AND ' . $fbWhere[$feedback] : '');
    $cols = 'w.wr_id, w.date_requested, w.status, w.em_number, w.stu_id, w.ic_no, w.state_id, w.site_id, w.bl_id, w.section, w.prob_group, w.work_performance, w.satisfaction_notes, w.satisfaction_date';
    $complaints = ppfFetchRows(
        "SELECT $cols FROM wr w WHERE $viewWhere ORDER BY w.wr_id DESC OFFSET $offset ROWS FETCH NEXT " . PPF_PER_PAGE . " ROWS ONLY",
        $params,
        'w.wr_id DESC',
        true
    );

    $filters = ppfFilterLists($f);
} catch (Throwable $e) {
    error_log('ppf_feedback: ' . $e->getMessage());
    ppfJson(500, ['ok' => false, 'error' => 'server_error']);
}

ppfJson(200, [
    'ok'         => true,
    'summary'    => $summary,
    'complaints' => $complaints,
    'pagination' => ['page' => $page, 'per_page' => PPF_PER_PAGE, 'total' => $total, 'pages' => $pages],
    'filters'    => $filters,
    'updated_at' => date('c'),
]);
