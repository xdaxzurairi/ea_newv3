<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idkat_infra = trim($_GET['idkat_infra'] ?? '');
$rows = dbFetchAll(
    "SELECT subinfra_id, subinfra_name FROM wr_sub_infra WHERE infra_id=? ORDER BY subinfra_name ASC",
    [$idkat_infra]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['subinfra_id'] ?? ''), 'name' => trim($r['subinfra_name'] ?? '')];
}
echo json_encode($result);
