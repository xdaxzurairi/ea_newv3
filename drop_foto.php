<?php
require_once __DIR__ . "/config/database.php";

try {
    $columns = ["foto1", "foto2", "foto3"];
    
    foreach ($columns as $col) {
        $sql = "IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('wr') AND name='" . $col . "')
                ALTER TABLE wr DROP COLUMN " . $col;
        
        $pdo->exec($sql);
        echo "[OK] Kolum '" . $col . "' berjaya dibuang\n";
    }
    
    echo "\n[SELESAI] Semua kolum foto telah dibuang dari jadual wr\n";
    
} catch (PDOException $e) {
    echo "[RALAT] " . $e->getMessage() . "\n";
    exit(1);
}
?>
