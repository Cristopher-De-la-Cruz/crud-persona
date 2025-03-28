<?php
namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
use App\Controllers\Controller;
require_once __DIR__ . '/../Auth/Token.php';
use Exception;

class PersonaController extends Controller {
    /**
     * get
     * 
     * Función para obtener personas,
     * filtros con page, limit, order_by, sort y search
     * 
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function get() {
        try{
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 5;
            $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'id';
            $sort = (isset($_GET['sort']) && strtolower($_GET['sort']) === 'desc') ? 'DESC' : 'ASC';
            $search = isset($_GET['search']) ? $_GET['search'] : '';

            $offset = ($page - 1) * $limit;

            $allowed_columns = ['id', 'tipo_documento', 'numero_documento', 'nombre', 'apellido', 'sexo', 'direccion', 'fecha_nacimiento'];
            if (!in_array($order_by, $allowed_columns)) {
                $order_by = 'id';
            }

            $sql = "SELECT * FROM personas WHERE CONCAT(nombre, ' ', apellido) LIKE ? ORDER BY $order_by $sort LIMIT ? OFFSET ?";
            $personas = self::query($sql, ["%$search%", $limit, $offset]);

            $total = self::query("SELECT COUNT(*) as total FROM personas", [])[0]->total;
            
            // información de paginación
            $total_pages = ceil($total / $limit);
            $pagination_info = [
                "current_page" => $page,
                "per_page" => $limit,
                "total_records" => $total,
                "total_pages" => $total_pages,
            ];

            return self::response(true, ["pagination" => $pagination_info, "data" => $personas], 200);

        } catch(Exception $e){
            return self::response(false, ["message" => $e->getMessage()], 500);
        }
    }

    /**
     * show
     * 
     * Función para obtener una persona por su id
     * 
     * @param mixed $id
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function show($id) {
        try{
            $persona = self::query("SELECT * FROM personas WHERE id = ?", [$id]);
            return self::response(true, @$persona[0], 200);
        } catch(Exception $e){
            return self::response(false, ["message" => $e->getMessage()], 500);
        }
    }

    /**
     * store
     * 
     * Función para crear a una nueva persona
     * 
     * requiere tipo_documento, numero_documento, nombre,
     * apellido, sexo, direccion, fecha_nacimiento
     * 
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function store(){
        try{
            $request = self::request();
            return $request;
            if(isset($request->tipo_documento) && isset($request->numero_documento) && isset($request->nombre) && isset($request->apellido)
                && isset($request->sexo) && isset($request->direccion) && isset($request->fecha_nacimiento)){
                self::query("INSERT INTO personas (tipo_documento, numero_documento, nombre, apellido, sexo, direccion, fecha_nacimiento)
                    VALUES (?, ?, ?, ?, ?, ?, ?)", [$request->tipo_documento, $request->numero_documento, $request->nombre, $request->apellido, $request->sexo, $request->direccion, $request->fecha_nacimiento]);
                return self::response(true, ["message" => "Persona guardada."], 200);
            } else{
                return self::response(false, ["message" => "Todos los campos son obligatorios."], 400);
            }

        } catch(Exception $e){
            return self::response(false, ["message" => $e->getMessage()], 500);
        }
    }

    /**
     * update
     * 
     * Función para actualizar una persona por su id
     * 
     * @param mixed $id
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function update($id){
        try{
            $request = self::request();
            if(isset($id) && isset($request->tipo_documento) && isset($request->numero_documento) && isset($request->nombre) && isset($request->apellido)
                && isset($request->sexo) && isset($request->direccion) && isset($request->fecha_nacimiento)){
                self::query("UPDATE personas SET tipo_documento = ?, numero_documento = ?, nombre = ?, apellido = ?, sexo = ?, direccion = ?, fecha_nacimiento = ?
                    WHERE id = ?", [$request->tipo_documento, $request->numero_documento, $request->nombre, $request->apellido, $request->sexo, $request->direccion, $request->fecha_nacimiento, $id]);
                return self::response(true, ["message" => "Persona Actualizada."], 200);

            } else{
                return self::response(false, ["message" => "Todos los campos son obligatorios."], 400);
            }

        } catch(Exception $e){
            return self::response(false, ["message" => $e->getMessage()], 500);
        }
    }
    
    /**
     * delete
     * 
     * Función para eliminar a una persona por su id
     * 
     * @param mixed $id
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function delete($id){
        try{
            self::query("DELETE FROM personas WHERE id = ?", [$id]);
            return self::response(true, ["message" => "Persona Eliminada."], 200);
        } catch(Exception $e){
            return self::response(false, ["message" => $e->getMessage()], 500);
        }
    }

}

?>
