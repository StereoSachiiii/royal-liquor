<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $pdo = null; 

    public static function getPdo(): PDO {
        if (self::$pdo === null) {
            $config = $GLOBALS['app_config'] ?? null;
            
            if (!$config) {
                throw new \Exception("System configuration not loaded. Ensure bootstrap.php is included.");
            }

            $dbConfig = $config['database'];
            
            $host = $dbConfig['host'];
            $db   = $dbConfig['name'];
            $user = $dbConfig['user'];
            $pass = $dbConfig['pass'];
            $port = $dbConfig['port'];
    

    // Use PostgreSQL (pgsql) DSN
    $dsn = "pgsql:host={$host};port={$port};dbname={$db};";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,                  // use native prepares
    ];

     try {
        self::$pdo = new PDO($dsn, $user, $pass, $options);
        return self::$pdo;

        } catch (PDOException $e) {
            throw new \App\Admin\Exceptions\DatabaseException(
                "Database Connection Error: " . $e->getMessage(),
                ['dsn' => $dsn, 'user' => $user],
                500,
                $e
            );
        }
}
    return self::$pdo;

   
    }
} ?>
