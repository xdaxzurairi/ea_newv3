<?php
declare(strict_types=1);

require_once __DIR__ . '/env.php';

$db_host     = getenv('DB_HOST')     ?: '';
$db_database = getenv('DB_DATABASE') ?: '';
$db_user     = getenv('DB_USER')     ?: '';
$db_pwd      = getenv('DB_PASSWORD') ?: '';
$db_port     = getenv('DB_PORT')     ?: '1433';

// Port eksplisit (host,port) supaya pemacu guna TCP terus dan tidak jatuh ke named pipes;
// dilangkau jika DB_HOST sudah ada port (,) atau nama instance (\).
if ($db_host !== '' && !str_contains($db_host, ',') && !str_contains($db_host, '\\')) {
    $db_host .= ',' . $db_port;
}

$pdo = null;
$lastException = null;

// Cuba pdo_sqlsrv dahulu (Microsoft driver), kemudian pdo_odbc sebagai fallback
$attempts = [];

if (extension_loaded('pdo_sqlsrv')) {
    $attempts[] = [
        'dsn' => "sqlsrv:Server=$db_host;Database=$db_database;TrustServerCertificate=true;LoginTimeout=5",
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

// Diagnostik sambungan sekejap-sekejap: setiap cubaan gagal dilog (label, hos, ms, kod native);
// satu percubaan semula selepas 300 ms sebelum beralih ke pemacu seterusnya.
foreach ($attempts as $att) {
    foreach ([1, 2] as $try) {
        $t0 = microtime(true);
        try {
            $pdo = new PDO($att['dsn'], $db_user, $db_pwd, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            break 2;
        } catch (PDOException $e) {
            $lastException = $e;
            error_log(sprintf(
                'DB cubaan %d gagal [%s] hos=%s %dms native=%s: %s',
                $try, $att['label'], $db_host, (int) round((microtime(true) - $t0) * 1000),
                (string) ($e->errorInfo[1] ?? '-'), $e->getMessage()
            ));
            if ($try === 1) {
                usleep(300000);
            }
        }
    }
}

if ($pdo === null) {
    if (php_sapi_name() === 'cli') {
        throw $lastException ?? new \RuntimeException('Tiada PDO driver SQL Server.');
    }
    if ($lastException) {
        error_log('DB connect gagal: ' . $lastException->getMessage());
    }
    header('Content-Type: text/html; charset=utf-8');
    echo '<p><strong>Ralat sambungan pangkalan data.</strong></p>';
    exit(1);
}

return $pdo;
