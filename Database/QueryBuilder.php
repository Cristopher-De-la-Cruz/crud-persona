<?php

namespace App\Database;

require_once __DIR__ . '/Database.php';
use App\Database\Database;
use Exception;

/**
 * QueryBuilder
 * 
 * Clase para ejecutar querys
 */
class QueryBuilder {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * query
     * 
     * Función que recibe un sql y parametros
     * ejecuta una query y retorna el resultado
     * 
     * @param mixed $sql
     * @param mixed $params
     * @param mixed $getJson
     * @throws \Exception
     * @return array|bool|int|string
     */
    public function query($sql, $params = [], $getJson = true) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
    
            if (preg_match('/^(SELECT|SHOW|DESCRIBE|PRAGMA)/i', $sql)) {
                $data = $stmt->fetchAll();
                return $getJson ? json_encode($data, JSON_PRETTY_PRINT) : $data;
            }
    
            return $stmt->rowCount();
        } catch (Exception $e) {
            throw new Exception("Error en la consulta: " . $e->getMessage());
        }
    }
    
}

?>
