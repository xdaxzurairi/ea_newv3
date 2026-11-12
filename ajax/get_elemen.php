<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$section = trim($_GET['section'] ?? '');
$idkampus = trim($_GET['idkampus'] ?? '');

$rows = dbFetchAll(
    "SELECT DISTINCT(a.prob_group), a.description FROM prob_group a, probtype b"
    . " WHERE a.section=? AND a.prob_group=b.prob_group"
    . " AND b.prob_type NOT IN (SELECT prob_type FROM exclude_site_pg_pt WHERE site_id=?)"
    . " ORDER BY a.prob_group ASC, a.description ASC",
    [$section, $idkampus]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['prob_group'] ?? ''), 'name' => trim($r['description'] ?? '')];
}
echo json_encode($result);
