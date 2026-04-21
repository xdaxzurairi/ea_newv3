<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idbangunan = trim($_GET['idbangunan'] ?? '');
$rows = dbFetchAll(
    "SELECT bl_id, name FROM bl WHERE bldg_id=? AND option2 IS NULL AND eaduan_publish='0' ORDER BY name ASC",
    [$idbangunan]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['bl_id'] ?? ''), 'name' => trim($r['name'] ?? '')];
}
echo json_encode($result);
