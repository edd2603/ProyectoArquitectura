<?php

require 'core/Router.php';
require 'core/Database.php';
$config = include('config/config.php');

// Verificar que base_url está definido
if (!isset($config['base_url'])) {
    die("Error: base_url no está definido en config.php.");
}

$base_url = $config['base_url'];

// Autoload para cargar las clases automáticamente
spl_autoload_register(function ($class) {
    $root = __DIR__; // Directorio raíz del proyecto
    $file = $root . '/' . str_replace('\\', '/', $class) . '.php';

    if (is_readable($file)) {
        require $file;
    } else {
        echo "Archivo de clase no encontrado: $file";
    }
});

// Inicializar el enrutador
use Core\Router;

$router = new Router();

// Definir las rutas
$router->add('', ['controller' => 'HomeController', 'action' => 'index']);
$router->add('registro', ['controller' => 'PacienteController', 'action' => 'registro']);
$router->add('confirmacion-registro', ['controller' => 'HomeController', 'action' => 'confirmacionRegistro']);
$router->add('login', ['controller' => 'LoginController', 'action' => 'login']);
$router->add('logout', ['controller' => 'LoginController', 'action' => 'logout']);
$router->add('dashboard', ['controller' => 'HomeController', 'action' => 'dashboard']);
$router->add('perfil', ['controller' => 'PerfilController', 'action' => 'perfil']);
$router->add('citas', ['controller' => 'CitasController', 'action' => 'mostrarOpciones']);
$router->add('citas/medicos/{tipo}', ['controller' => 'CitasController', 'action' => 'mostrarMedicos']);
$router->add('citas/calendario/{medico_id}', ['controller' => 'CitasController', 'action' => 'mostrarCalendario']);
$router->add('citas/confirmar', ['controller' => 'CitasController', 'action' => 'confirmarCita']);
$router->add('citas/obtenerEventos/{medico_id}', ['controller' => 'CitasController', 'action' => 'obtenerEventos']);
$router->add('citas/cancelar', ['controller' => 'CitasController', 'action' => 'cancelarCita']);
$router->add('historia-clinica', ['controller' => 'HistoriaClinicaController', 'action' => 'solicitarHistoriaClinica']);
$router->add('informacion', ['controller' => 'HomeController', 'action' => 'informacion']);


// Obtener la URL actual
$url = $_SERVER['QUERY_STRING'];

// Despachar la ruta solicitada
$router->dispatch($url);
