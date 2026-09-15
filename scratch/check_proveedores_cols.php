<?php
$c = require __DIR__ . '/../config/config.php';
$pdo = new PDO('mysql:host=' . $c['db']['host'] . ';dbname=' . $c['db']['database'], $c['db']['username'], $c['db']['password']);
$cols = $pdo->query('SHOW COLUMNS FROM proveedores')->fetchAll(PDO::FETCH_ASSOC);
print_r($cols);
