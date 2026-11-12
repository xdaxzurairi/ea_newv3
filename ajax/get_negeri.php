<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$rows = dbFetchAll("SELECT state_id, name FROM state WHERE eaduan_publish='1' ORDER BY name ASC");

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['state_id'] ?? ''), 'name' => trim($r['name'] ?? '')];
}
echo json_encode($result);
