<?php
namespace App\Libraries;

require_once __DIR__ . '/JWT.php';
require_once __DIR__ . '/Key.php';
require_once __DIR__ . '/ExpiredException.php';
require_once __DIR__ . '/SignatureInvalidException.php';
require_once __DIR__ . '/../Libraries/Env.php';


use App\Libraries\Env;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Exception;

Env::load();
/**
 * Token
 * 
 * Clase creada para generar y verificar tokens con JWT
 */
class Token {
    private static $secret_key;
    private static $issuer;
    private static $audience;

    public static function init() {
        self::$secret_key = getenv('SECRET_KEY') ?: 'default_key';
        self::$issuer = getenv('JWT_ISSUER') ?: 'default_issuer';
        self::$audience = getenv('JWT_AUDIENCE') ?: 'default_audience';
    }

    /**
     * generate
     * 
     * Función que recibe la data y la expiración
     * Genera un token a partir de los datos obtenidos
     * Retorna un token en String
     * 
     * @param mixed $data
     * @param mixed $exp
     * @return string
     */
    public static function generate($data, $exp = false) {
        self::init();

        $payload = [
            "iss" => self::$issuer,
            "aud" => self::$audience,
            "data" => $data
        ];

        // Expiración si es especificada
        if($exp){
            $payload["iat"] = time();
            $payload["exp"] = time()+$exp;
        }

        return JWT::encode($payload, self::$secret_key, 'HS256');
    }

    /**
     * verify
     * 
     * Recibe un token para decodificarlo
     * Retorna la data del token o un valor nulo.
     * 
     * @param string $token
     * @return \stdClass|null
     */
    public static function verify($token) {
        self::init();
        try {
            return JWT::decode($token, new Key(self::$secret_key, 'HS256'));
        } catch (ExpiredException $e) {
            return null;
        } catch (SignatureInvalidException $e){
            return null;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
