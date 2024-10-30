<?php
namespace App\Controllers;

use Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MedicamentosController {
    private $db;
    private $base_url;

    public function __construct() {
        $config = include(__DIR__ . '/../../config/config.php');
        $this->db = new Database($config);
        $this->base_url = $config['base_url'];

        // Mostrar errores
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    public function solicitarMedicamentos() {
        session_start();
        header('Content-Type: application/json'); // Asegura que la respuesta siempre sea JSON

        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['status' => 'error', 'message' => 'No estás autenticado.']);
            return;
        }

        $usuario = $_SESSION['usuario'];
        $paciente = $this->db->fetch("SELECT id, correo, nombre FROM pacientes WHERE usuario = ?", [$usuario]);

        if (!$paciente) {
            echo json_encode(['status' => 'error', 'message' => 'No se encontró información del paciente.']);
            return;
        }

        $paciente_id = $paciente['id'];
        $correo_paciente = $paciente['correo'];
        $nombre_paciente = utf8_decode($paciente['nombre']); // Decodificar nombre para caracteres especiales

        $consulta = $this->db->fetch("SELECT id FROM consultas WHERE paciente_id = ? ORDER BY fecha_consulta DESC LIMIT 1", [$paciente_id]);

        if (!$consulta) {
            echo json_encode(['status' => 'error', 'message' => 'No tienes consultas recientes.']);
            return;
        }

        $consulta_id = $consulta['id'];
        $medicamentos = $this->db->fetchAll("
            SELECT m.nombre_medicamento, m.dosis, m.frecuencia, d.nombre AS nombre_drogueria, d.direccion 
            FROM medicamentos m
            INNER JOIN droguerias d ON d.id = m.drogueria_id
            WHERE m.consulta_id = ? AND m.estado = 1
        ", [$consulta_id]);

        if (empty($medicamentos)) {
            echo json_encode(['status' => 'error', 'message' => 'No tienes medicamentos pendientes para enviar.']);
            return;
        }

        // Generar el PDF
        require(__DIR__ . '/../../lib/fpdf/fpdf.php');
        $upload_dir = __DIR__ . '/../../uploads';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_path = $upload_dir . '/medicamentos_' . $nombre_paciente . '.pdf';

        $pdf = new \FPDF();
        $pdf->AddPage();

        // Encabezado con información de la clínica
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('Orden de Medicamentos'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 8, utf8_decode('Clínica de Salud Integral'), 0, 1, 'C');
        $pdf->Cell(0, 8, utf8_decode('Dirección: Calle 123, Ciudad | Teléfono: 123-456-7890'), 0, 1, 'C');
        $pdf->Ln(10);

        // Información del paciente
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(30, 8, utf8_decode('Paciente:'), 0, 0);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $nombre_paciente, 0, 1);
        $pdf->Ln(5);

        // Tabla de medicamentos
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(55, 10, 'Medicamento', 1, 0, 'C', true);
        $pdf->Cell(30, 10, 'Dosis', 1, 0, 'C', true);
        $pdf->Cell(35, 10, 'Frecuencia', 1, 0, 'C', true);
        $pdf->Cell(70, 10, utf8_decode('Droguería'), 1, 1, 'C', true);

        $pdf->SetFont('Arial', '', 10);
        $pdf->SetFillColor(245, 245, 245);

        foreach ($medicamentos as $med) {
            $pdf->Cell(55, 10, utf8_decode($med['nombre_medicamento']), 1, 0, 'C', true);
            $pdf->Cell(30, 10, utf8_decode($med['dosis']), 1, 0, 'C', true);
            $pdf->Cell(35, 10, utf8_decode($med['frecuencia']), 1, 0, 'C', true);
            $pdf->MultiCell(70, 10, utf8_decode($med['nombre_drogueria'] . ', ' . $med['direccion']), 1, 'L', true);
        }

        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, utf8_decode('Recuerda presentar esta orden al momento de reclamar tus medicamentos.'), 0, 1, 'C');

        $pdf->Output('F', $file_path);

        if (file_exists($file_path)) {
            $this->enviarCorreoConPDF($correo_paciente, $file_path);
            $this->db->execute("UPDATE medicamentos SET estado = 0 WHERE consulta_id = ?", [$consulta_id]);
            echo json_encode(['status' => 'success', 'message' => 'Solicitud completada correctamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al generar el PDF en la ruta especificada.']);
        }
    }

private function enviarCorreoConPDF($correo_paciente, $file_path) {
    require(__DIR__ . '/../../lib/PHPMailer/src/PHPMailer.php');
    require(__DIR__ . '/../../lib/PHPMailer/src/SMTP.php');
    require(__DIR__ . '/../../lib/PHPMailer/src/Exception.php');

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jhon.ospina1685@ucaldas.edu.co';
        $mail->Password = 'ryqm suef ozra uscb';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('jhon.ospina1685@ucaldas.edu.co', 'Sistema de Diagnóstico');
        $mail->addAddress($correo_paciente);

        if (file_exists($file_path)) {
            $mail->addAttachment($file_path);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'El archivo PDF no fue encontrado para adjuntarlo.']);
            return;
        }

        $mail->isHTML(true);
        $mail->Subject = utf8_decode('Medicamentos de la Última Cita');
        $mail->Body = utf8_decode('Adjunto encontrarás el PDF con los medicamentos de tu última cita y la dirección de la droguería correspondiente.');

        $mail->send();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al enviar el correo: ' . $mail->ErrorInfo]);
    }
}

}
