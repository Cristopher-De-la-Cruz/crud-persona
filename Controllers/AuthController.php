<?php
namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../Auth/Token.php';
use App\Controllers\Controller;
use App\Libraries\Token;
use Exception;

/**
 * AuthController
 * 
 * Clase de ejemplo de manejo de Tokens
 * 
 */
class AuthController extends Controller {

    /**
     * generateToken
     * 
     * Función de ejemplo de como crear un token.
     * Genera el token agregandole la data (obligatorio) y el tiempo de expiración (opcional).
     * Retorna el token como string
     * 
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function generateToken(){
        try{
            $token = Token::generate([
                "type" => "UserExample"
            ], 3600*24);
    
            return self::response(true, ["token" => $token], 200);
        } catch(Exception $e){
            return self::response(false, ["error" => "Ocurrió un error", "message" => $e->getMessage()], 500);
        }
    }

    /**
     * readToken
     * 
     * Función de ejemplo de como leer un token.
     * Obtiene el token del header con la función del controller.
     * Verifica y decodifica el token retornando su contenido.
     * 
     * @return array{body: mixed, status: int, success: bool}
     */
    public static function readToken(){
        $token = self::getToken();
        if($token){
            $decoded = Token::verify($token);
            if ($decoded) {
                return self::response(true, $decoded->data, 200);
            }
        }
        return self::response(false, ["message" => "Token inválido"], 401);
    }
}

?>