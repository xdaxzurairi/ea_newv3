<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idkampus = trim($_GET['idkampus'] ?? '');
$infra = trim($_GET['infra'] ?? '');

$siteIdNorm = strtoupper($idkampus);
$excludeInf = in_array($siteIdNorm, ['A03', 'B01']) && $infra !== '2';

$excludeInfEle = '';
if ($excludeInf) {
    $excludeInfEle = " AND UPPER(TRIM(a.section)) NOT LIKE 'INF ELE%'"
        . " AND UPPER(TRIM(a.section)) NOT LIKE '%INFRA ELEKTRIK%'"
        . " AND UPPER(TRIM(a.section)) NOT LIKE '%INF ELEKTRIK%'"
        . " AND UPPER(TRIM(a.description)) NOT LIKE 'INF ELE%'"
        . " AND UPPER(TRIM(a.description)) NOT LIKE '%INFRA ELEKTRIK%'"
        . " AND UPPER(TRIM(a.description)) NOT LIKE '%INF ELEKTRIK%'";
}

$sql = "SELECT DISTINCT a.section, a.description FROM section a"
    . " INNER JOIN probtype b ON b.section = a.section"
    . " WHERE a.section NOT IN ('PREVENTIVE MAINT','INF MEC','ICT')"
    . $excludeInfEle
    . " AND b.prob_type NOT IN (SELECT prob_type FROM exclude_site_pg_pt WHERE site_id=?)"
    . " ORDER BY a.description ASC";

$rows = dbFetchAll($sql, [$idkampus]);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['section'] ?? ''), 'name' => trim($r['description'] ?? '')];
}
echo json_encode($result);
