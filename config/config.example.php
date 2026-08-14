<?php

return [
    'app' => [
        'name'        => 'Control de Proformas y Facturas',
        'env'         => 'local',
        'debug'       => true,
        'url'         => 'http://proformas.local',
        'timezone'    => 'America/Tegucigalpa',
        'dias_alerta_gestion' => 15,
        'dias_alerta_factura' => 8,
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
        'lifetime' => 120,
    ],
];
