

<?php
/**
 * Configuración general de la aplicación.
 * IMPORTANTE: en producción, mueve las credenciales a variables de entorno
 * (por ejemplo con un archivo .env fuera del document root) en lugar de
 * dejarlas escritas aquí.

return [
    'app' => [
        'name'        => 'Control de Proformas y Facturas',
        'env'         => 'local',           // local | production
        'debug'       => true,               // false en producción
        'url'         => 'http://proformas.local',
        'timezone'    => 'America/Tegucigalpa',
        'dias_alerta_gestion' => 15,  // días máx. esperados entre fin de trabajo y revisión de cotización
        'dias_alerta_factura' => 8
    ],

    'db' => [
        'driver'   => 'mysql',
        'host'     => '127.0.0.1',
        'port'     => '3306',
        'database' => 'control_proformas',
        'username' => 'root',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    'session' => [
        'name'     => 'proformas_session',
        'lifetime' => 120, // minutos
    ],
];


 **/



return [
    'app' => [
        'name' => 'Test', 'env' => 'local', 'debug' => true,
        'url' => 'http://proformas.local', 'timezone' => 'America/Tegucigalpa',
        'dias_alerta_gestion' => 15,
        'dias_alerta_factura' => 8,
    
        'iis_permisos_identidad' => null,
    ],
    'db' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'control_proformas',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4',
    ],
    'session' => ['name' => 'test_session', 'lifetime' => 120, 'version' => 2,],
    
];


