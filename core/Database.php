<?php

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/config.php';
            $db = $config['db'];

            $dsn = "{$db['driver']}:host={$db['host']};port={$db['port']};dbname={$db['database']};charset={$db['charset']}";

            try {
                self::$instance = new PDO($dsn, $db['username'], $db['password'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // No exponer credenciales ni detalles internos en pantalla.
                error_log('Error de conexión a BD: ' . $e->getMessage());
                http_response_code(500);
                die('No fue posible conectar a la base de datos. Revisa config/config.php y que MySQL esté activo.');
            }
        }

        return self::$instance;
    }
}
