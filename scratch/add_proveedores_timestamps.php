<?php
$c = require __DIR__ . '/../config/config.php';
$pdo = new PDO('mysql:host=' . $c['db']['host'] . ';dbname=' . $c['db']['database'], $c['db']['username'], $c['db']['password']);

$sql = "
ALTER TABLE `proveedores`
  ADD COLUMN IF NOT EXISTS `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  ADD COLUMN IF NOT EXISTS `eliminado_en` TIMESTAMP NULL DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `eliminado_por` INT(11) DEFAULT NULL;
";

try {
    $pdo->exec($sql);
    echo "Columnas agregadas con éxito.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$cols = $pdo->query('SHOW COLUMNS FROM proveedores')->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
