<?php
namespace App\Controllers;

use App\Database\QueryBuilder;

require_once __DIR__ . '/../Database/QueryBuilder.php'; // Se agrega el archivo manualmente por falta de composer

class Example {
    private $db;

    public function __construct() {
        $this->db = new QueryBuilder();
    }

    public function getUsuarios() {
        $usuarios = $this->db->query("SELECT * FROM personas WHERE id = ?", [1], true);
        header('Content-Type: application/json');
        print_r(json_encode($usuarios));
    }
}

// Instanciar la clase y llamar al método
$example = new Example();
$example->getUsuarios();
?>
