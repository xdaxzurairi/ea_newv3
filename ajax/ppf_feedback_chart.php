<?php
/**
 * eAduan v3 – Portal PPF: data graf maklum balas (JSON, baca-sahaja, semua kampus).
 * Skop: aduan berstatus Com atau I (seperti portal lama).
 * GET group=site|month, state_id, site_id, zone_id, section, prob_group, date_from, date_to
 */
require_once __DIR__ . '/../includes/ppf_api.php';

ppfEnforce('json');
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    header('Allow: GET');
    ppfJson(405, ['ok' => false, 'error' => 'method_not_allowed']);
}

$group = ppfStr('group', '/^[a-z]{1,10}$/') ?: 'site';
if (!in_array($group, ['site', 'month'], true)) {
    ppfInvalid();
}

$f = ppfReadCommonFilters();

$params = [];
$where  = ppfCommonWhere($f, $params);
$where[] = "UPPER(RTRIM(w.status)) IN ('COM', 'I')";
$baseWhere = implode(' AND ', $where);

$counts = "SUM(CASE WHEN w.work_performance = 1 THEN 1 ELSE 0 END) AS c_ok,
           SUM(CASE WHEN w.work_performance = 2 THEN 1 ELSE 0 END) AS c_bad,
           COUNT(*) AS c_all";

try {
    if ($group === 'site') {
        $rows = dbFetchAll(
            "SELECT RTRIM(w.site_id) AS k, RTRIM(MAX(si.name)) AS label, $counts
             FROM wr w LEFT JOIN site si ON si.state_id = w.state_id AND si.site_id = w.site_id
             WHERE $baseWhere GROUP BY RTRIM(w.site_id) ORDER BY COUNT(*) DESC, RTRIM(w.site_id)",
            $params
        );
    } else {
        $rows = dbFetchAll(
            "SELECT CONVERT(char(7), w.date_requested, 23) AS k, CONVERT(char(7), w.date_requested, 23) AS label, $counts
             FROM wr w WHERE $baseWhere AND w.date_requested IS NOT NULL
             GROUP BY CONVERT(char(7), w.date_requested, 23) ORDER BY k",
            $params
        );
    }
    $filters = ppfFilterLists($f);
} catch (Throwable $e) {
    error_log('ppf_feedback_chart: ' . $e->getMessage());
    ppfJson(500, ['ok' => false, 'error' => 'server_error']);
}

$totals = ['memuaskan' => 0, 'tidak_memuaskan' => 0, 'tiada' => 0, 'total' => 0];
$series = [];
foreach ($rows as $r) {
    $ok  = (int) $r['c_ok'];
    $bad = (int) $r['c_bad'];
    $all = (int) $r['c_all'];
    $none = $all - $ok - $bad;
    $totals['memuaskan']       += $ok;
    $totals['tidak_memuaskan'] += $bad;
    $totals['tiada']           += $none;
    $totals['total']           += $all;
    $key = trim((string) $r['k']);
    $series[] = [
        'key'             => $key,
        'label'           => ppfLabel((string) $r['label']) ?: ($key !== '' ? $key : 'Tiada kampus'),
        'memuaskan'       => $ok,
        'tidak_memuaskan' => $bad,
        'tiada'           => $none,
        'total'           => $all,
    ];
}

ppfJson(200, [
    'ok'         => true,
    'group'      => $group,
    'totals'     => $totals,
    'series'     => $series,
    'filters'    => $filters,
    'updated_at' => date('c'),
]);
