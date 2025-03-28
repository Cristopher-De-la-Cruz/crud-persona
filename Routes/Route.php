<?php
namespace App\Routes;

require_once __DIR__ . './Api.php';

use Exception;

/**
 * Route
 * 
 * Clase de rutas del servidor
 */
class Route {
    private static $routes = [];

    public function __construct() {

    }

    /**
     * addRoute
     * 
     * Función para agregar una nueva ruta,
     * solicita obligatoriamente un metodo, url y acción a ejecutar,
     * se le puede agregar middlewares y points que no necesariamente sean "api".
     * 
     * @param mixed $method
     * @param mixed $uri
     * @param mixed $action
     * @param mixed $middlewares
     * @param mixed $point
     * @return void
     */
    public static function addRoute($method, $uri, $action, $middlewares = [], $point = 'api/') {
        try {
            if (isset($method) && isset($uri) && isset($action)) {
                $dir_path = dirname(__DIR__);
                $dir = basename($dir_path);
                $host = '/' . $dir . '/' . $point;
                $full_uri = $host . $uri;

                if (!isset(self::$routes[$method][$full_uri])) {
                    self::$routes[$method][$full_uri] = ['action' => $action, 'middlewares' => $middlewares];
                }
            }
        } catch (Exception $e) {
            http_response_code(500);
        }
    }


    /**
     * getRoutes
     * 
     * Función para retornar y visualizar todas las rutas creadas a detalle.
     * 
     * @return array
     */
    public static function getRoutes(){
        return self::$routes;
    }

    /**
     * handleRequest
     * 
     * Función que verifica la url llamada, con su metodo,
     * revisa si la ruta está registrada, ejecuta los middlewares si fueron 
     * y posteriormente la acción definida. 
     * 
     * @return void
     */
    public static function handleRequest() {
        header('Content-Type: application/json');

        try {
            $uri = $_SERVER['REQUEST_URI'];
            $method = $_SERVER['REQUEST_METHOD'];
            $routes = self::$routes;

            $parsedUrl = parse_url($uri);
            $path = $parsedUrl['path']; // Extrae solo el path sin query params

            if (isset($routes[$method])) {
                foreach ($routes[$method] as $route => $handler) {
                    $pattern = preg_replace('/:\w+/', '([^\/]+)', str_replace('/', '\/', $route));

                    if (preg_match('/^' . $pattern . '$/', $path, $matches)) {
                        array_shift($matches);

                        // ✅ Ejecutar Middlewares antes del handler
                        foreach ($handler['middlewares'] as $middleware) {
                            if (is_callable([$middleware, 'handle'])) {
                                $middleware::handle();
                            }
                        }

                        // ✅ Ejecutar la acción
                        if (is_callable($handler['action'])) {
                            $response = call_user_func_array($handler['action'], $matches);
                        } else {
                            list($class, $method) = $handler['action'];
                            $response = call_user_func_array([new $class, $method], $matches);
                        }

                        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                        return;
                    }
                }
            }

            // Ruta no encontrada
            http_response_code(404);
            echo json_encode(["success" => false, "body" => ["message" => "Ruta no encontrada"], "status" => 404], JSON_PRETTY_PRINT);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "body" => ["error" => "Ocurrió un error", "message" => $e->getMessage()], "status" => 500], JSON_PRETTY_PRINT);
        }
    }

}

?>
