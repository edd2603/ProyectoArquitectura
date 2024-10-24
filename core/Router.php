<?php

namespace Core;

class Router {
    protected $routes = [];

    // Método para agregar una ruta
public function add($route, $params) {
    // Convertir las barras '/' en barras invertidas para expresiones regulares
    $route = preg_replace('/\//', '\\/', $route);
    
    // Convertir {parametro} en regex, ej: {id} => (?P<id>[a-zA-Z0-9_-]+)
    $route = preg_replace('/\{([a-z_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $route);
    
    // Añadir delimitadores de inicio y fin
    $route = '/^' . $route . '$/i';
    
    $this->routes[$route] = $params;
}

    // Método para obtener todas las rutas
    public function getRoutes() {
        return $this->routes;
    }

    // Método para hacer coincidir la URL con una ruta registrada
    public function match($url) {
        // Eliminar las barras finales de la URL
        $url = trim($url, '/');

        // Iterar sobre las rutas registradas
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Filtrar las variables dinámicas de los matches
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                    }
                }
                return $params;
            }
        }

        // Si no se encuentra la ruta, devolver false
        return false;
    }

    // Método para despachar la URL a su controlador y acción correspondientes
public function dispatch($url) {
    $url = $this->removeQueryString($url);  // Eliminar parámetros de consulta
    $params = $this->match($url);
    
    if ($params) {
        $controller = $params['controller'];
        $controller = "App\\Controllers\\" . $controller;
        if (class_exists($controller)) {
            $controllerObject = new $controller();
            $action = $params['action'];

            if (method_exists($controllerObject, $action)) {
                unset($params['controller']);
                unset($params['action']);
                if (!empty($params)) {
                    call_user_func_array([$controllerObject, $action], [$params]);
                } else {
                    call_user_func_array([$controllerObject, $action], []);
                }
            } else {
                echo "Método $action no encontrado en el controlador $controller.";
            }
        } else {
            echo "Controlador $controller no encontrado.";
        }
    } else {
        echo "Ruta no encontrada.";
    }
}


    // Método para eliminar los parámetros GET de la URL
    protected function removeQueryString($url) {
        if ($url != '') {
            $parts = explode('&', $url, 2);  // Dividir la URL y los parámetros GET
            if (strpos($parts[0], '=') === false) {
                return $parts[0];  // Si no hay un '=', es una URL limpia
            }
        }
        return '';  // Si hay parámetros GET, devolver una URL vacía
    }
}
