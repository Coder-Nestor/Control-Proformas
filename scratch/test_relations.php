<?php
$c = require __DIR__ . '/../config/config.php';
$db = $c['db'];
$pdo = new PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['database'], $db['username'], $db['password']);

echo "GESTIONES TABLE:\n";
print_r($pdo->query('DESCRIBE gestiones')->fetchAll(PDO::FETCH_ASSOC));

echo "\nPROFORMAS TABLE:\n";
print_r($pdo->query('DESCRIBE proformas')->fetchAll(PDO::FETCH_ASSOC));
