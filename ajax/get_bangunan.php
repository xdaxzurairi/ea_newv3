<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idkampus = trim($_GET['idkampus'] ?? '');
$rows = dbFetchAll(
    "SELECT bldg_id, bl_name FROM bldg WHERE site_id=? AND eaduan_publish='0' AND bldg_id != 'B0542' AND bldg_id != 'B0514' ORDER BY bl_name ASC",
    [$idkampus]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['bldg_id'] ?? ''), 'name' => trim($r['bl_name'] ?? '')];
}
echo json_encode($result);
