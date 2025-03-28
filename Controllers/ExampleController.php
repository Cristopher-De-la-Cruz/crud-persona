<?php
namespace App\Controllers;

require_once __DIR__ . '/Controller.php';
use App\Controllers\Controller;
use Exception;

/**
 * ExampleController
 * 
 * Clase de ejemplo
 * 
 */
class ExampleController extends Controller {

    /**
     * helloworld
     * 
     * http://localhost/FW/php/api/helloworld?to=guy
     * 
     * @return array{body: mixed, status: bool, success: int}
     */
    public static function helloworld(){
        try{
            $to = isset($_GET['to']) ? $_GET['to'] : '';

            $toMessage = $to != '' ? " to $to" : '';

            return self::response(true, ["message" => "hello world$toMessage 😉✔"], 200);
        } catch(Exception $e){
            return self::response(false, ["error" => "Error? ._.", "message" => $e->getMessage()], 500);
        }
    }

    /**
     * save
     * 
     * Función de ejemplo de almacenamiento de archivos usando saveFile
     * 
     * @return array{body: mixed, status: bool, success: int}
     */
    public static function save()
    {
        try {
            if(isset($_FILES['file'])){
                $save = self::saveFile($_FILES['file'], 'files');
                if($save['success']){
                    return self::response(true, ["message" => "archivo guardado", "result" => $save], 200);
                } else{
                    return self::response(false, ["message" => "Error al guardar"], 400);
                }
            } else{
                return self::response(false, ["message" => "No se envió file"], 400);
            }
        } catch (Exception $e) {
            return self::response(false, ["error" => "Error", "message" => $e->getMessage()], 500);
        }
    }
    
    /**
     * saveImg
     * 
     * Función de ejemplo de almacenamiento de imagenes,
     * transformandolas a webp y posteriormente almacenandolas con saveImage.
     * 
     * @return array{body: mixed, status: bool, success: int}
     */
    public static function saveImg()
    {
        try {
            if(isset($_FILES['image'])){
                $result = self::toWebp($_FILES['image']); //Transformar a Webp
                if ($result['success']) {
                    if(self::saveFile($result['webp'], 'images')['success']){
                        return self::response(true, ["message" => "imagen guardada"], 200);
                    } else{
                        return self::response(false, ["message" => "Error al guardar"], 400);
                    }
                } else {
                    return self::response(false, ["message" => "Error al transformar"], 400);
                }
            } else{
                return self::response(false, ["message" => "No se envió una imagen"], 400);
            }
        } catch (Exception $e) {
            return self::response(false, ["error" => "Error", "message" => $e->getMessage()], 500);
        }
    }


}

?>