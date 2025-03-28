<?php
namespace App\Libraries;

/**
 * Env
 * 
 * Clase para trabajar y acceder a los datos del .env
 * 
 */
class Env {
    /**
     * load
     * 
     * Carga los datos del .env
     * Si existe los ingresa en el env
     * @param mixed $file
     * @return void
     */
    public static function load($file = __DIR__ . '/../.env') {
        if (!file_exists($file)) return;

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                putenv(trim($key) . '=' . trim($value));
            }
        }
    }
}
?>
