<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Paciente</title>
    <link rel="stylesheet" href="<?= $this->base_url ?>/assets/css/dashboard.css"> <!-- Ruta absoluta a los estilos -->
    <link rel="stylesheet" href="<?= $this->base_url ?>/assets/css/perfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Iconos -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="dashboard-container">
    <!-- Barra lateral -->
    <aside class="sidebar">
        <h2>Paciente</h2>
        <ul>
            <li><a href="#" class="menu-link" data-page="informacion"><i class="fas fa-info-circle"></i> Información General</a></li>
            <li><a href="<?= $this->base_url ?>/perfil"><i class="fas fa-user"></i> Perfil</a></li>
            <li><a href="#" class="menu-link" data-page="citas"><i class="fas fa-calendar-days"></i> Solicitar Cita</a></li>
            <li><a href="#" id="solicitarHistoriaClinica"><i class="fas fa-file-medical"></i> Solicitar Historia Clínica</a></li>
            <li><a href="<?= $this->base_url ?>/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
        </ul>
    </aside>

    <!-- Contenido principal dinámico -->
    <main class="main-content" id="content-area">
        <?php echo $content; ?> <!-- Este es el contenido dinámico que se carga -->
    </main>
</div>

<script>
$(document).ready(function() {
    $('.menu-link').on('click', function(e) {
        e.preventDefault(); // Evitar comportamiento predeterminado
        var page = $(this).data('page'); // Obtener el valor de data-page

        // Realizar una solicitud AJAX para cargar la página correspondiente
        $.ajax({
            url: '<?= $this->base_url ?>/' + page,  // URL del controlador
            method: 'GET',
            success: function(response) {
                $('#content-area').html(response);  // Cargar el contenido en el área principal
            },
            error: function() {
                alert('Error al cargar el contenido.');
            }
        });
    });
    
    // Al hacer clic en el enlace para solicitar la historia clínica
    $('#solicitarHistoriaClinica').on('click', function(e) {
        e.preventDefault();  // Evitar que el enlace siga su comportamiento predeterminado

        // Mostrar una alerta de confirmación antes de solicitar la historia clínica
        Swal.fire({
            title: '¿Deseas solicitar tu historia clínica?',
            text: 'Se te enviará un PDF con tu historial médico al correo registrado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Solicitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Si se confirma, realizar la solicitud AJAX
                $.ajax({
                    url: '<?= $this->base_url ?>/historia-clinica',  // URL del controlador que gestiona la solicitud
                    method: 'GET',
                    success: function(response) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Tu historial clínico ha sido enviado a tu correo.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'No se pudo enviar tu historial clínico.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
});

</script>
