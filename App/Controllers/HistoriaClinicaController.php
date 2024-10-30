<?php

namespace App\Controllers;

use Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class HistoriaClinicaController {
    private $db;
    private $base_url;

    public function __construct() {
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config);
        $this->base_url = $config['base_url'];
    }

    public function solicitarHistoriaClinica() {
        session_start();

        // Verificar si el usuario está logueado
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . $this->base_url . '/login');
            exit();
        }

        // Obtener el correo y el ID del paciente desde la sesión
        $usuario = $_SESSION['usuario'];
        $paciente = $this->db->fetch("SELECT id, correo, nombre FROM pacientes WHERE usuario = ?", [$usuario]);
        $paciente_id = $paciente['id'];
        $correo_paciente = $paciente['correo'];
        $nombre_paciente = $paciente['nombre'];

        // Insertar una historia clínica de prueba
       /* $this->insertarHistoriaClinicaPrueba($paciente_id);*/

        // Obtener el historial médico de los últimos 6 meses
        $historial_medico = $this->db->fetchAll("
            SELECT fecha, descripcion FROM historial_clinico 
            WHERE paciente_id = ? AND fecha >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        ", [$paciente_id]);

// Generar el PDF
require(__DIR__ . '/../../lib/fpdf/fpdf.php');

// Verificar si la carpeta uploads existe, si no, la creamos
$upload_dir = __DIR__ . '/../../uploads';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Guardar el PDF en un archivo temporal
$file_path = $upload_dir . '/historial_' . $nombre_paciente . '.pdf';

$pdf = new \FPDF();
$pdf->AddPage();

// Establecer el diseño del encabezado
$pdf->SetFont('Arial', 'B', 16);
$pdf->SetTextColor(33, 37, 41); // Color de texto oscuro
$pdf->Cell(0, 10, utf8_decode('Historial Médico de los últimos 6 meses'), 0, 1, 'C');
$pdf->Ln(10); // Salto de línea

// Información del paciente (puedes agregar más detalles si lo deseas)
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetTextColor(0, 102, 204); // Color de texto azul
$pdf->Cell(40, 10, 'Paciente:');
$pdf->SetFont('Arial', '', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(40, 10, utf8_decode($nombre_paciente)); // Cambia esto por el nombre del paciente si lo tienes
$pdf->Ln(10); // Salto de línea

// Crear la tabla para mostrar el historial médico
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(200, 220, 255); // Fondo azul claro
$pdf->SetDrawColor(50, 50, 100);   // Bordes oscuros
$pdf->Cell(60, 10, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(130, 10, utf8_decode('Descripción'), 1, 1, 'C', true);

// Contenido del historial médico
$pdf->SetFont('Arial', '', 12);
foreach ($historial_medico as $entry) {
    // Celda de la fecha (no es necesario ajustar)
    $pdf->Cell(60, 10, utf8_decode($entry['fecha']), 1);
    
    // Celda de la descripción usando MultiCell para manejar texto largo
    $y = $pdf->GetY(); // Guardar la posición Y actual
    $pdf->SetXY(70, $y); // Mover la posición X a donde comienza la celda de descripción
    $pdf->MultiCell(130, 10, utf8_decode($entry['descripcion']), 1); // MultiCell permite texto en varias líneas
    $pdf->Ln();
}

// Guardar el PDF en el archivo temporal
$pdf->Output('F', $file_path);


        // Enviar el PDF por correo
        if (file_exists($file_path)) {
            $this->enviarCorreoConPDF($correo_paciente, $file_path);
        } else {
            echo "Error al generar el PDF.";
        }

        // Mostrar mensaje final
        echo json_encode(['status' => 'success', 'message' => 'El historial clínico ha sido enviado a tu correo.']);
    }

   private function enviarCorreoConPDF($correo_paciente, $file_path) {
    // Incluir la clase PHPMailer
    require(__DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php');
    require(__DIR__ . '/../../lib/PHPMailer/src/SMTP.php');
    require(__DIR__ . '/../../lib/PHPMailer/src/Exception.php');

    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jhon.ospina1685@ucaldas.edu.co'; // Gmail que usamos en las pruebas
        $mail->Password   = 'ryqm suef ozra uscb';            // Contraseña de la cuenta Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y destinatario
        $mail->setFrom('jhon.ospina1685@ucaldas.edu.co', 'Sistema de Diagnóstico');
        $mail->addAddress($correo_paciente);

        // Adjuntar el PDF generado
        if (file_exists($file_path)) {
            $mail->addAttachment($file_path);
        } else {
            echo "El archivo PDF no fue encontrado para adjuntarlo.";
            return;
        }

        // Contenido del correo con codificación UTF-8
        $mail->isHTML(true);
        $mail->Subject = utf8_decode('Historial Clínico');
        $mail->Body    = utf8_decode('Adjunto encontrarás tu historial clínico de los últimos 6 meses.');

        // Enviar correo
        $mail->send();
        echo "Correo enviado con éxito.";
    } catch (Exception $e) {
        echo "El correo no pudo ser enviado. Error: {$mail->ErrorInfo}";
    }
}

/*    private function insertarHistoriaClinicaPrueba($paciente_id) {
        $this->db->execute("INSERT INTO historial_clinico (paciente_id, fecha, descripcion) VALUES (?, NOW(), ?)", 
            [$paciente_id, 'Descripción de prueba para el historial clínico']);
    }*/
}
