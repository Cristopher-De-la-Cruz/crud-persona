<?php
namespace App\Controllers;

require_once __DIR__ . '/../Database/QueryBuilder.php'; // Se agrega el archivo manualmente por falta de composer
require_once __DIR__ . '/../Database/MysqliDB.php';
use App\Database\QueryBuilder;
use App\Database\MysqliDB;
use Exception;


/**
 * Controller
 * 
 * Clase controller, con múltiples funciones de base para trabajar en la creación de Apis
 */
class Controller {
    protected static $db;
    protected static $mysqli_db;

    /**
     * init
     * 
     * Inicializa la clase Database para acceder a ella.
     * @return void
     */
    public static function init() {
        if (!self::$db) {
            self::$db = new QueryBuilder();
        }
        
        if (!self::$mysqli_db) {
            self::$mysqli_db = new MysqliDB();
        }
    }

    /**
     * query
     * 
     * Función que recibe un query y parametros
     * ejecuta el query usando PDO y retorna el resultado,
     * si getJson es true entonces decodifica el json.
     * 
     * @param mixed $query
     * @param mixed $params
     * @param mixed $getJson
     * @return mixed
     */
    public static function query($query = '', $params = [], $getJson = true) {
        self::init();
        
        $result = self::$db->query($query, $params, $getJson);
        
        return $getJson ? json_decode($result, false) : $result;
    }

    /**
     * mysqli_query
     * 
     * Función que recibe un query y parametros
     * ejecuta el query usando mysqli y retorna el resultado,
     * si getJson es true entonces decodifica el json.
     * 
     * 
     * @param mixed $query
     * @param mixed $params
     * @param mixed $getJson
     * @return mixed
     */
    public static function mysqli_query($query = '', $params = [], $getJson = true) {
        self::init();
        if($getJson){
            return json_decode(self::$mysqli_db->query($query, $params, $getJson), false);
        }
        return self::$mysqli_db->query($query, $params, $getJson);
    }

    /**
     * request
     * 
     * Función que obtiene todos los datos enviados por el request body
     * Retorna los datos en formato json
     * 
     * @return mixed
     */
    public static function request(){
        $input = @file_get_contents('php://input');

        return json_decode($input);
    }

    /**
     * response
     * 
     * Retorna un array con un formato de respuesta para las Apis.
     * 
     * @param bool $success
     * @param mixed $body
     * @param int $status
     * @return array{body: mixed, status: bool, success: int}
     */
    public static function response($success = true, $body = [], $status = 200){
        
        return ["success" => $success, "body" => $body, "status" => $status];
    }

    /**
     * getToken
     * 
     * Accede a los headers y obtiene el token
     * retorna el token conseguido o un valor nulo.
     * 
     * @return string|null
     */
    public static function getToken() {
        $headers = getallheaders();
        return isset($headers['Authorization']) ? trim(str_replace('Bearer ', '', $headers['Authorization'])) : null;
    }

    /**
     * saveFile
     * 
     * Función para almacenar archivos en la carpeta public,
     * retorna array con datos para acceder al archivo almacenado.
     * 
     * @param mixed $file 
     * @param string $path
     * @param bool $originalName
     * @return array{file_full_path: string, file_name: string, file_path: string, success: bool|array{message: string, success: bool}}
     */
    public static function saveFile($file, $path = '', $originalName = false)
    {
        try {
            $basePath = __DIR__ . '/../Public/';
            $finalPath = rtrim($basePath . $path, '/') . '/';

            if (!is_dir($finalPath)) {
                mkdir($finalPath, 0777, true);
            }

            $fileName = $originalName ? basename($file['name']) : time() . $file['size'] . $file['name'];
            $fileDestination = $finalPath . $fileName;

            // Si el archivo fue subido por HTTP
            if (is_uploaded_file($file['tmp_name'])) {
                $moved = move_uploaded_file($file['tmp_name'], $fileDestination);
            } else {
                // Si el archivo es generado en el servidor
                $moved = rename($file['tmp_name'], $fileDestination);
            }
            $file_path = rtrim("/Public/$path", '/') . '/';
            $file_path = $file_path.$fileName;

            if ($moved) {
                return ["success" => true, "file_name" => $fileName, "file_path" => $file_path, "file_full_path" => $fileDestination];
            }
            return ["success" => false, "message" => "Error al guardar archivo"];
        } catch (Exception $e) {
            error_log("Error al guardar el archivo: " . $e->getMessage());
            return ["success" => false, "message" => $e->getMessage()];
        }
    }

    /**
     * toWebp
     * 
     * Función para transformar imagenes y gifs a formato webp,
     * almacena temporalmente el nuevo archivo y
     * retorna array con datos para redirigir el archivo creado.
     * 
     * @param mixed $img
     * @throws \Exception
     * @return array{message: string, success: bool|array{success: bool, webp: array{error: int, full_path: string, name: string, size: bool|int, tmp_name: string, type: string}}|array{success: bool, webp: mixed}}
     */
    public static function toWebp($img) {
        try {
            if (!isset($img['tmp_name']) || !is_file($img['tmp_name'])) {
                throw new Exception("El archivo no es válido.");
            }

            $imageInfo = getimagesize($img['tmp_name']);
            if ($imageInfo === false) {
                throw new Exception("No es un archivo de imagen válido.");
            }

            $mime = $imageInfo['mime'];

            // 🚀 Si la imagen ya es WebP, devolver directamente el archivo sin conversión
            if ($mime === 'image/webp') {
                return ["success" => true, "webp" => $img];

            }

            // Procesar solo si no es WebP
            switch ($mime) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($img['tmp_name']);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($img['tmp_name']);
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($img['tmp_name']);
                    break;
                default:
                    throw new Exception("Formato no soportado. Solo JPG, PNG y GIF.");
            }

            $fileNameWithoutExt = pathinfo($img['name'], PATHINFO_FILENAME);

            $webpFolder = __DIR__ . '/../Public/temp/';
            if (!is_dir($webpFolder)) {
                mkdir($webpFolder, 0777, true);
            }

            $webpPath = $webpFolder . $fileNameWithoutExt . '.webp';

            if (!imagewebp($image, $webpPath, 80)) {
                throw new Exception("Error al convertir la imagen a WebP.");
            }

            imagedestroy($image);

            if (!file_exists($webpPath)) {
                throw new Exception("El archivo WebP no se generó correctamente.");
            }

            $webpFile = [
                "name" => $fileNameWithoutExt . '.webp',
                "full_path" => $fileNameWithoutExt . '.webp',
                "type" => "image/webp",
                "tmp_name" => $webpPath,
                "error" => 0,
                "size" => filesize($webpPath),
            ];

            return ["success" => true, "webp" => $webpFile];
        } catch (Exception $e) {
            return ["success" => false, "message" => $e->getMessage()];
        }
    }



}

?>
