<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idblok = trim($_GET['idblok'] ?? '');
$rows = dbFetchAll(
    "SELECT fl_id, name FROM fl WHERE bl_id=? ORDER BY name ASC",
    [$idblok]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['fl_id'] ?? ''), 'name' => trim($r['name'] ?? '')];
}
echo json_encode($result);
