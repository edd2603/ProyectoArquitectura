<?php

namespace App\Controllers;

use Core\Database;

class HomeController {
    private $db;
    private $base_url;

    public function __construct() {
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config);
        $this->base_url = $config['base_url'];
    }

    // Método principal que se carga en la ruta '/'
    public function index() {
        session_start();

        ob_start(); // Iniciar el buffer de salida

        if (isset($_SESSION['usuario'])) {
            // Si el usuario ha iniciado sesión, redirigir al dashboard
            header('Location: ' . $this->base_url . '/dashboard');
            exit();
        } else {
            // Si no ha iniciado sesión, mostrar el formulario de login
            require 'app/views/login.php';
        }

        $content = ob_get_clean(); // Capturar el contenido del buffer
        require 'app/views/layout.php'; // Usar el layout con el contenido
    }

    // Método para cargar el dashboard
    public function dashboard() {
        session_start();

        // Verificar si el usuario ha iniciado sesión
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . $this->base_url . '/login');
            exit();
        }

        ob_start(); // Iniciar el buffer de salida

        // Mostrar el dashboard con el contenido deseado
        $content = "<h1>Bienvenido al Dashboard, " . $_SESSION['usuario'] . "</h1>";
        require 'app/views/dashboard.php'; // Mostrar la vista del dashboard
    }

    // Método para mostrar información general (citas y medicamentos pendientes)
public function informacion() {
    session_start();

    // Verificar si el usuario ha iniciado sesión
    if (!isset($_SESSION['usuario'])) {
        header('Location: ' . $this->base_url . '/login');
        exit();
    }

    // Obtener el ID del paciente desde la sesión
    $usuario = $_SESSION['usuario'];
    $paciente = $this->db->fetch("SELECT id FROM pacientes WHERE usuario = ?", [$usuario]);
    $paciente_id = $paciente['id'];

    // Obtener la próxima cita médica confirmada que esté en el futuro
    $proxima_cita = $this->db->fetch("SELECT fecha_cita, tipo_cita, estado 
                                      FROM citas_medicas 
                                      WHERE paciente_id = ? AND estado = 'confirmada' 
                                      AND fecha_cita > NOW() 
                                      ORDER BY fecha_cita ASC 
                                      LIMIT 1", [$paciente_id]);

    // Obtener los medicamentos pendientes por reclamar (por ejemplo, con un estado "pendiente")
    $medicamentos_pendientes = $this->db->fetchAll("SELECT nombre_medicamento, dosis, frecuencia 
                                                    FROM medicamentos 
                                                    WHERE consulta_id IN (SELECT id FROM consultas WHERE paciente_id = ?)
                                                    AND estado = '1'", [$paciente_id]);

    // Generar el contenido de la página con clases CSS estilo "card"
    ob_start();
    $content = "<div class='cards-container'>"; // Contenedor principal de las cards

    // Tarjeta de próxima cita
    $content .= "<div class='card'>";
    $content .= "<h2>Próxima Cita Médica</h2>";
    if ($proxima_cita) {
        $content .= "<p class='info-detail'><strong>Fecha:</strong> " . $proxima_cita['fecha_cita'] . "</p>";
        $content .= "<p class='info-detail'><strong>Tipo:</strong> " . ucfirst($proxima_cita['tipo_cita']) . "</p>";
        $content .= "<p class='info-detail'><strong>Estado:</strong> " . ucfirst($proxima_cita['estado']) . "</p>";
    } else {
        $content .= "<p class='info-detail'>No tienes citas confirmadas próximas.</p>";
    }
    $content .= "</div>"; // Fin de la card de citas

    // Tarjeta de medicamentos pendientes
    $content .= "<div class='card'>";
    $content .= "<h2>Medicamentos por Reclamar</h2>";
    if (count($medicamentos_pendientes) > 0) {
        $content .= "<ul class='info-list'>";
        foreach ($medicamentos_pendientes as $medicamento) {
            $content .= "<li><strong>Medicamento:</strong> " . $medicamento['nombre_medicamento'] . " - <strong>Dosis:</strong> " . $medicamento['dosis'] . " - <strong>Frecuencia:</strong> " . $medicamento['frecuencia'] . "</li>";
        }
        $content .= "</ul>";
    } else {
        $content .= "<p class='info-detail'>No tienes medicamentos pendientes.</p>";
    }
    $content .= "</div>"; // Fin de la card de medicamentos

    $content .= "</div>"; // Fin del contenedor principal

    echo $content;
    echo ob_get_clean();
}



    // Método para confirmar el registro y mostrar SweetAlert
    public function confirmacionRegistro() {
        echo "
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: '¡Registro Exitoso!',
                    text: 'Tu registro ha sido completado. Ahora puedes iniciar sesión.',
                    icon: 'success',
                    confirmButtonText: 'Iniciar Sesión',
                    timer: 3000,
                    timerProgressBar: true
                }).then((result) => {
                    if (result.isConfirmed || result.dismiss === Swal.DismissReason.timer) {
                        window.location.href = '" . $this->base_url . "/login';
                    }
                });
            });
        </script>
        ";
    }
}
