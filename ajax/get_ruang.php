<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idfloor = trim($_GET['idfloor'] ?? '');
$idblok = trim($_GET['idblok'] ?? '');
$rows = dbFetchAll(
    "SELECT rm_id, name FROM rm WHERE fl_id=? AND bl_id=? ORDER BY name ASC",
    [$idfloor, $idblok]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['rm_id'] ?? ''), 'name' => trim($r['name'] ?? '')];
}
echo json_encode($result);
