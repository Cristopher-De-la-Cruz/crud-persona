<?php

namespace App\Database;

require_once __DIR__ . '/../Libraries/Env.php';


use App\Libraries\Env;
use Exception;
use mysqli;

Env::load();
class MysqliDB {
    private $conexion;
    private $host;
    private $user;
    private $password;
    private $database;

    public function __construct() {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->user = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->database = getenv('DB_NAME') ?: 'database';

        $this->conexion = new mysqli($this->host, $this->user, $this->password, $this->database);

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }

    /**
     * query
     * 
     * Función que recibe un query y parametros
     * ejecuta el query usando mysqli y retorna el resultado,
     * si getJson es true entonces decodifica el json.
     * 
     * @param mixed $query
     * @param mixed $params
     * @param mixed $getJson
     * @throws \Exception
     * @return array|bool|int|string
     */
    public function query($query = '', $params = [], $getJson = true) {
        $stmt = $this->conexion->prepare($query);
        
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $this->conexion->error);
        }

        if (!empty($params)) {
            $types = "";
            foreach ($params as $param) {
                $types .= (is_int($param) ? "i" : (is_double($param) ? "d" : "s"));
            }
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $fetch = $result->fetch_all(MYSQLI_ASSOC);
            if ($getJson) {
                return json_encode($fetch, JSON_PRETTY_PRINT);
            }

            return $fetch;
        }

        return $stmt->affected_rows;
    }

    public function close() {
        $this->conexion->close();
    }

    public function __destruct() {
        $this->close();
    }
}

?>