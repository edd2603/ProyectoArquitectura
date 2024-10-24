<?php

namespace App\Controllers;

use Core\Database;

class CitasController {
    private $db;
    private $base_url;

    public function __construct() {
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config);
        $this->base_url = $config['base_url'];
    }

    // Mostrar las opciones de cita (presencial, virtual, especialista)
    public function mostrarOpciones() {
        ob_start();
        require 'app/views/citas/opciones.php';  // Vista con las opciones
        $content = ob_get_clean();
        echo $content;
    }

    // Mostrar la lista de médicos según el tipo de cita
    public function mostrarMedicos($params) {
        $tipo = $params['tipo'];

        if ($tipo === 'Especialista') {
            $medicos = $this->db->fetchAll("SELECT id, nombre, especialidad FROM personal_medico WHERE especialidad != 'General'");
        } else {
            $medicos = $this->db->fetchAll("SELECT id, nombre, especialidad FROM personal_medico WHERE especialidad = ?", [$tipo]);
        }

        if (!$medicos) {
            $medicos = []; // Evita null si no hay médicos
        }

        header('Content-Type: application/json');
        echo json_encode($medicos);
        exit;
    }

    // Mostrar el calendario de un médico seleccionado
    public function mostrarCalendario($params) {
        $medico_id = $params['medico_id'];
        $citas = $this->db->fetchAll("SELECT fecha_cita FROM citas_medicas WHERE medico_id = ? AND estado != 'cancelada'", [$medico_id]);

        if (!$citas) {
            $citas = [];
        }

        ob_start();
        require 'app/views/citas/calendario.php';  // Vista con el calendario
        $content = ob_get_clean();
        echo $content;
    }

    // Obtener eventos de citas reservadas
    public function obtenerEventos($params) {
        $medico_id = $params['medico_id'];
        $citas = $this->db->fetchAll("SELECT id, fecha_cita AS start, 'Cita reservada' AS title FROM citas_medicas WHERE medico_id = ? AND estado != 'cancelada'", [$medico_id]);

        header('Content-Type: application/json');
        echo json_encode($citas);
        exit;
    }

    // Confirmar la cita
    public function confirmarCita() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $medico_id = $_POST['medico_id'] ?? null;
            $fecha = $_POST['fecha'] ?? null;
            $paciente_id = $_POST['paciente_id'] ?? null;

            if ($medico_id && $fecha && $paciente_id) {
                $resultado = $this->db->execute(
                    "INSERT INTO citas_medicas (medico_id, paciente_id, fecha_cita, estado) VALUES (?, ?, ?, 'confirmada')",
                    [$medico_id, $paciente_id, $fecha]
                );

                header('Content-Type: application/json');
                if ($resultado) {
                    echo json_encode(['status' => 'success', 'message' => 'Cita confirmada']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Error al guardar la cita']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            }
            exit;
        }
    }

    // Cancelar la cita
    public function cancelarCita() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cita_id = $_POST['cita_id'] ?? null;

            if ($cita_id) {
                $resultado = $this->db->execute(
                    "UPDATE citas_medicas SET estado = 'cancelada' WHERE id = ?",
                    [$cita_id]
                );

                header('Content-Type: application/json');
                if ($resultado) {
                    echo json_encode(['status' => 'success', 'message' => 'Cita cancelada']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No se pudo cancelar la cita']);
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
            }
            exit;
        }
    }
}
