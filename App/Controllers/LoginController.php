<?php

namespace App\Controllers;

use Core\Database;

class LoginController {
    private $db;
    private $base_url;

    public function __construct() {
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config); 
        $this->base_url = $config['base_url'];
    }

    // Acción para iniciar sesión
    public function login() {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = htmlspecialchars($_POST['usuario']);
            $password = $_POST['password'];

            $user = $this->db->fetch("SELECT * FROM pacientes WHERE usuario = ?", [$usuario]);

            if ($user && password_verify($password, $user['password'])) {
                // Usuario y contraseña correctos
                $_SESSION['usuario'] = $user['usuario'];  // Guardar el usuario en la sesión
                $_SESSION['id'] = $user['id'];  // Guardar el usuario en la sesión
                header('Location: ' . $this->base_url . '/dashboard');  // Redirigir al dashboard
                exit();
            } else {
                // Datos incorrectos: Mostrar alerta SweetAlert
                echo "
                <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Datos incorrectos',
                            text: 'Usuario o contraseña incorrectos. Por favor, inténtalo de nuevo.',
                            confirmButtonText: 'Intentar de nuevo'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '" . $this->base_url . "/';
                            }
                        });
                    });
                </script>
                ";
            }
        } else {
            require 'app/views/login.php';
        }
    }

    // Acción para cerrar sesión
    public function logout() {
        session_start();
        session_unset();  // Limpiar todas las variables de sesión
        session_destroy();  // Destruir la sesión
        header('Location: ' . $this->base_url . '/');  // Redirigir a la página principal o de inicio de sesión
        exit();
    }

}
