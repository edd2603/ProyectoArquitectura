<?php
    $config = include(__DIR__ . '/../../config/config.php');
    $base_url = $config['base_url'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Registro</title>
</head>
<body>

<form id="form-registro" method="https://2e3c-191-156-33-163.ngrok-free.app/api/saveUser">
    <input type="text" name="nombre" placeholder="Nombre" required><br>
    <input type="text" name="cedula" placeholder="Cédula" required><br>
    <input type="text" name="usuario" placeholder="Usuario" required><br>
    <input type="password" name="password" placeholder="Contraseña" required><br>
    <button type="submit">Registrarse</button>
</form>

<script>
    // Manejar el formulario de registro
    $('#form-registro').on('submit', function(e) {
        e.preventDefault();  // Evitar que el formulario se envíe de la forma tradicional

        // Obtener los datos del formulario
        var formData = $(this).serialize();  // Serializar el formulario

        // Enviar los datos por AJAX
        $.ajax({
            url: '<?= $this->base_url ?>/registro',
            method: 'POST',
            data: formData,
            success: function(response) {
                // Analizar la respuesta del servidor
                if (typeof response !== "object") {
                    response = JSON.parse(response);
                }

                if (response.status === 'success') {
                    // Mostrar SweetAlert de éxito
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message
                    }).then(() => {
                        // Redirigir en caso de éxito
                        window.location.href = '<?= $this->base_url ?>/confirmacion-registro';
                    });
                } else {
                    // Mostrar SweetAlert de error
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.fire('Error', 'Hubo un error en la solicitud', 'error');
            }
        });
    });
</script>

</body>
</html>


