<?php
$c = require __DIR__ . '/config/config.php';
$db = $c['db'];
$dsn = 'mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['database'] . ';charset=' . $db['charset'];
$pdo = new PDO($dsn, $db['username'], $db['password']);

$numbers = $pdo->query("SELECT DISTINCT n_cotizacion FROM gestiones WHERE n_cotizacion IS NOT NULL AND n_cotizacion != '' ORDER BY n_cotizacion")->fetchAll(PDO::FETCH_COLUMN);
echo "Cotizaciones en gestiones:\n";
foreach ($numbers as $num) {
    echo "- $num\n";
}

echo "\nTrabajos con gestion join:\n";
$stmt = $pdo->query("SELECT t.id, t.descripcion, t.valor, g.n_cotizacion, t.proforma_id FROM trabajos t INNER JOIN gestiones g ON g.id = t.gestion_id ORDER BY g.n_cotizacion, t.id");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "NINGUNO\n";
} else {
    foreach ($rows as $row) {
        echo "{$row['id']}\t{$row['descripcion']}\t{$row['valor']}\t{$row['n_cotizacion']}\t{$row['proforma_id']}\n";
    }
}

echo "\nPrueba query exacta para 'S06603':\n";
$stmt = $pdo->prepare("SELECT t.id, t.descripcion, t.valor, g.n_cotizacion, t.proforma_id FROM trabajos t INNER JOIN gestiones g ON g.id = t.gestion_id WHERE TRIM(COALESCE(g.n_cotizacion, '')) = :numero AND t.proforma_id IS NULL ORDER BY g.id, t.orden");
$stmt->execute(['numero' => 'S06603']);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "NINGUNO\n";
} else {
    foreach ($rows as $row) {
        echo "{$row['id']}\t{$row['descripcion']}\t{$row['valor']}\t{$row['n_cotizacion']}\t{$row['proforma_id']}\n";
}
}
