<?php

namespace App\Middleware;

require_once __DIR__ . '/../Auth/JWT.php';
require_once __DIR__ . '/../Auth/Key.php';
require_once __DIR__ . '/../Auth/SignatureInvalidException.php';
require_once __DIR__ . '/../Auth/ExpiredException.php';
require_once __DIR__ . '/../Libraries/Env.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException; // Importar la excepción
use App\Libraries\Env;
use Exception;

Env::load();

/**
 * AuthMiddleware
 * 
 * Clase Middleware para verificar el requerimiento de un token en una ruta
 */
class AuthMiddleware {

    /**
     * handle
     * 
     * Verifica el token, si fue ingresado,
     * si es valido, firmado correctamente, expirado, etc.
     * 
     * @return \stdClass
     */
    public static function handle() {
        $secret_key = getenv('SECRET_KEY') ?: 'default_key';
        $headers = getallheaders();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["success" => false, "body" => ["message" => "Token requerido"], "status" => 401]);
            exit;
        }

        $token = str_replace('Bearer ', '', $headers['Authorization']);

        try {
            $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));

            return $decoded;
        } catch (SignatureInvalidException $e) {
            http_response_code(401);
            echo json_encode(["success" => false, "body" => ["message" => "Token inválido o expirado"], "status" => 401]);
            exit;
        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode(["success" => false, "body" => ["message" => "Token expirado"], "status" => 401]);
            exit;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["success" => false, "body" => ["message" => "Token inválido o expirado"], "status" => 401]);
            exit;
        }
    }
}

?>
