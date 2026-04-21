<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

$db_host     = getenv('DB_HOST')     ?: '10.0.36.97';
$db_database = getenv('DB_DATABASE') ?: 'FMSDEV';
$db_user     = getenv('DB_USER')     ?: '';
$db_pwd      = getenv('DB_PASSWORD') ?: '';

$pdo = null;
$lastException = null;

// Cuba pdo_sqlsrv dahulu (Microsoft driver), kemudian pdo_odbc sebagai fallback
$attempts = [];

if (extension_loaded('pdo_sqlsrv')) {
    $attempts[] = [
        'dsn' => "sqlsrv:Server=$db_host;Database=$db_database",
        'label' => 'pdo_sqlsrv',
    ];
}

if (extension_loaded('pdo_odbc')) {
    foreach (['SQL Server', 'ODBC Driver 17 for SQL Server', 'ODBC Driver 18 for SQL Server', 'SQL Server Native Client 11.0'] as $driver) {
        $attempts[] = [
            'dsn' => "odbc:Driver={$driver};Server=$db_host;Database=$db_database;",
            'label' => "pdo_odbc ($driver)",
        ];
    }
}

foreach ($attempts as $att) {
    try {
        $pdo = new PDO($att['dsn'], $db_user, $db_pwd, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        break;
    } catch (PDOException $e) {
        $lastException = $e;
    }
}

if ($pdo === null) {
    if (php_sapi_name() === 'cli') {
        throw $lastException ?? new \RuntimeException('Tiada PDO driver SQL Server.');
    }
    $show = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)
         || !empty($_GET['db_debug']);
    header('Content-Type: text/html; charset=utf-8');
    echo '<p><strong>Ralat sambungan pangkalan data.</strong></p>';
    if ($show && $lastException) {
        echo '<pre>' . htmlspecialchars($lastException->getMessage()) . '</pre>';
        echo '<p>Extension aktif: pdo_sqlsrv=' . (extension_loaded('pdo_sqlsrv') ? 'Ya' : 'Tidak')
           . ', pdo_odbc=' . (extension_loaded('pdo_odbc') ? 'Ya' : 'Tidak') . '</p>';
    }
    exit(1);
}

return $pdo;
