<?php

namespace App\Controllers;

use Core\Database;

class PacienteController {
    private $db;
    private $base_url;

    public function __construct() {
        // Cargar config.php
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config); 
        if (!isset($config['base_url'])) {
            die("Error: base_url no está definido.");
        }
        $this->base_url = $config['base_url'];  // Asegúrate de que base_url está definido correctamente
    }

public function registro() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validar datos del formulario
        $nombre = htmlspecialchars($_POST['nombre']);
        $cedula = htmlspecialchars($_POST['cedula']);
        $usuario = htmlspecialchars($_POST['usuario']);
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Encriptamos la contraseña

        // Verificar si la cédula o el usuario ya existen
        $existingPaciente = $this->db->fetch("SELECT * FROM pacientes WHERE cedula = ? OR usuario = ?", [$cedula, $usuario]);

        // Asegurarnos de que la respuesta sea JSON con codificación UTF-8
        header('Content-Type: application/json; charset=utf-8');

        if ($existingPaciente) {
            // Retornar JSON con el estado de error
            echo json_encode(['status' => 'error', 'message' => 'La cédula o el usuario ya están registrados.'], JSON_UNESCAPED_UNICODE);
        } else {
            // Insertar los datos en la base de datos
            $this->db->execute("INSERT INTO pacientes (nombre, cedula, usuario, password) VALUES (?, ?, ?, ?)", [$nombre, $cedula, $usuario, $password]);

            // Retornar JSON con el estado de éxito
            echo json_encode(['status' => 'success', 'message' => 'Registro exitoso.'], JSON_UNESCAPED_UNICODE);
        }
        exit();  // Detener el script aquí
    } else {
        // Si es una petición GET, mostramos el formulario
        require 'app/views/registro.php';
    }
}

}
