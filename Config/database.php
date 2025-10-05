<?php
// Config/database.php

// Carga el autoloader de Composer si aún no lo has hecho
require __DIR__ . '/../vendor/autoload.php';

// Carga la biblioteca de dotenv para que lea el archivo .env
// con la sintaxis correcta para las versiones recientes
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

return [
    'driver'    => 'mysql',
    'host'      => $_ENV['DB_HOST'],
    'database'  => $_ENV['DB_DATABASE'],
    'user'      => $_ENV['DB_USER'],
    'password'  => $_ENV['DB_PASSWORD'],
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
];