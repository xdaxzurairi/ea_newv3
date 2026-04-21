<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$prob_group = trim($_GET['prob_group'] ?? '');
$idkampus = trim($_GET['idkampus'] ?? '');

$rows = dbFetchAll(
    "SELECT prob_type, description FROM probtype"
    . " WHERE prob_group=? AND prob_type NOT IN (SELECT prob_type FROM exclude_site_pg_pt WHERE site_id=?)"
    . " ORDER BY description ASC",
    [$prob_group, $idkampus]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['prob_type'] ?? ''), 'name' => trim($r['description'] ?? '')];
}
echo json_encode($result);
