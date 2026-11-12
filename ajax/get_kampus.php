<?php
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

$idnegeri = trim($_GET['idnegeri'] ?? '');
$rows = dbFetchAll(
    "SELECT site_id, name FROM site WHERE state_id=? AND eaduan_publish='1' ORDER BY site_id ASC",
    [$idnegeri]
);

$result = [];
foreach ($rows as $r) {
    $result[] = ['id' => trim($r['site_id'] ?? ''), 'name' => trim($r['name'] ?? '')];
}
echo json_encode($result);
