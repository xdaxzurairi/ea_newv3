<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$rows = dbFetchAll("SELECT infra_id, infra_name FROM wr_kat_infra ORDER BY infra_name ASC");

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['infra_id'] ?? ''), 'name' => trim($r['infra_name'] ?? '')];
}
echo json_encode($result);
