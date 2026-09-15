<?php
$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);
echo "PROVEEDORES COLUMNS:\n";
print_r($pdo->query('DESCRIBE proveedores')->fetchAll(PDO::FETCH_ASSOC));
echo "\nSAMPLE PROVEEDORES:\n";
print_r($pdo->query('SELECT * FROM proveedores LIMIT 10')->fetchAll(PDO::FETCH_ASSOC));
