<?php
// Definir la ruta base para los archivos de PHPMailer
define('BASE_PATH', __DIR__ . '/PHPMailer/src/');

// Importar las clases necesarias de PHPMailer
require BASE_PATH . 'PHPMailer.php';
require BASE_PATH . 'SMTP.php';
require BASE_PATH . 'Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Crear una instancia de PHPMailer
$mail = new PHPMailer(true);

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';               // Servidor SMTP
    $mail->SMTPAuth   = true;                           // Autenticación SMTP
    $mail->Username   = 'jhon.ospina1685@ucaldas.edu.co'; // Correo desde el que se enviará
    $mail->Password   = 'ryqm suef ozra uscb';          // Contraseña o contraseña de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Protocolo TLS
    $mail->Port       = 587;                            // Puerto TCP

    // Destinatario
    $mail->setFrom('jhon.ospina1685@ucaldas.edu.co', 'Socobuses');
    $mail->addAddress('gaius2601@gmail.com');           // Destinatario

    // Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = 'Prueba de correo desde PHPMailer';
    $mail->Body    = 'Este es un correo de prueba enviado usando PHPMailer.';

    // Enviar el correo
    $mail->send();
    echo 'Correo enviado correctamente';
} catch (Exception $e) {
    echo "Error al enviar el correo: {$mail->ErrorInfo}";
}
