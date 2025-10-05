<?php

namespace App\Core;
use PDO;
use PDOException;

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            $config = include __DIR__ . '/../../Config/database.php';
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            try {
                $pdo = new PDO($dsn, $config['user'], $config['password']);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                self::$connection = $pdo;
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    public function __clone() {
        trigger_error('Clonación no permitida.', E_USER_ERROR); }   
}