<?php

namespace App\Controllers;

use Core\Database;

class PerfilController {
    private $db;
    private $base_url;

    public function __construct() {
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config);
        $this->base_url = $config['base_url'];
    }

    public function perfil() {
        session_start();

        // Verificar si el usuario ha iniciado sesión
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . $this->base_url . '/login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Guardar los datos actualizados
            $nombre = htmlspecialchars($_POST['nombre']);
            $correo = htmlspecialchars($_POST['correo']);
            $telefono = htmlspecialchars($_POST['telefono']);
            $usuario = $_SESSION['usuario'];

            // Iniciar la consulta SQL
            $sql = "UPDATE pacientes SET nombre = ?, correo = ?, telefono = ?";
            $params = [$nombre, $correo, $telefono];

            // Si se proporciona una nueva contraseña, la añadimos a la consulta
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $sql .= ", password = ?";
                $params[] = $password;
            }

            // Subir imagen de perfil si se proporciona
            if (!empty($_FILES['foto']['name'])) {
                $foto_nombre = $_FILES['foto']['name'];
                $foto_tmp = $_FILES['foto']['tmp_name'];
                $upload_dir = __DIR__ . '/../../uploads/';

                // Verificar que la carpeta uploads existe, si no, crearla
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $foto_path = $upload_dir . basename($foto_nombre);
                if (move_uploaded_file($foto_tmp, $foto_path)) {
                    // Ruta relativa para almacenar en la base de datos
                    $foto_db_path = 'uploads/' . basename($foto_nombre);
                    $sql .= ", foto = ?";
                    $params[] = $foto_db_path;
                } else {
                    echo "Error al subir la imagen.";
                }
            }

            // Finalizar la consulta SQL con la cláusula WHERE
            $sql .= " WHERE usuario = ?";
            $params[] = $usuario;

            // Ejecutar la consulta
            $this->db->execute($sql, $params);

            // Redirigir a la página del perfil
            header('Location: ' . $this->base_url . '/perfil');
            exit();
        }

        // Obtener datos del paciente
        $usuario = $_SESSION['usuario'];
        $paciente = $this->db->fetch("SELECT * FROM pacientes WHERE usuario = ?", [$usuario]);

        ob_start();
        require 'app/views/perfil.php';
        $content = ob_get_clean();
        require 'app/views/dashboard.php';
    }
}
