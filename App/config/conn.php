<?php
namespace config;

use PDO;
use PDOException;

class conn
{
    private static $pdo = null;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            $host = 'localhost'; 
            $db   = 'payments'; 
            $user = 'root'; 
            $pass = ''; 
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        return self::$pdo;
    }
}