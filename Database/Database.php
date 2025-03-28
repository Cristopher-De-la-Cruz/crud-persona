<?php

namespace App\Database;

require_once __DIR__ . '/../Libraries/Env.php';

use App\Libraries\Env;
use PDO;
use PDOException;

Env::load();
/**
 * Database
 * 
 * Clase Database con PDO
 */
class Database {
    private static $instance = null;
    private $pdo;

    
    private function __construct() {
        $dsn = 'mysql:host=' . (getenv('DB_HOST') ?: 'localhost') . ';dbname=' . (getenv('DB_NAME') ?: 'database') . ';charset=utf8mb4';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';

        try {
            $this->pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    /**
     * getInstance
     * 
     * genera una instancia propia y la retorna
     * 
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * getConnection
     * 
     * retorna la conexión
     * 
     * @return PDO
     */
    public function getConnection() {
        return $this->pdo;
    }
}


?>
