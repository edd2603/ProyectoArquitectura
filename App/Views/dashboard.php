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
                <li><a href="#" id="solicitarMedicamentos"><i class="fas fa-pills"></i> Solicitar Medicamentos</a></li>

                <li><a href="<?= $this->base_url ?>/logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </aside>

        <!-- Contenido principal dinámico -->
        <main class="main-content" id="content-area">
            <?php echo $content; ?> <!-- Este es el contenido dinámico que se carga -->
        </main>
    </div>

   <script>
    console.log("SweetAlert script loaded and ready");

$(document).ready(function() {
    $('.menu-link').on('click', function(e) {
        e.preventDefault();
        var page = $(this).data('page');

        $.ajax({
            url: '<?= $this->base_url ?>/' + page,
            method: 'GET',
            success: function(response) {
                $('#content-area').html(response);
            },
            error: function() {
                alert('Error al cargar el contenido.');
            }
        });
    });

    // Solicitar historia clínica
    $('#solicitarHistoriaClinica').on('click', function(e) {
        e.preventDefault();

        Swal.fire({
            title: '¿Deseas solicitar tu historia clínica?',
            text: 'Se te enviará un PDF con tu historial médico al correo registrado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Solicitar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando solicitud...',
                    text: 'Por favor, espera un momento.',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '<?= $this->base_url ?>/historia-clinica',
                    method: 'GET',
                    success: function(response) {
                        Swal.close(); // Cierra el loader cuando se recibe la respuesta

                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'Tu historial clínico ha sido enviado a tu correo.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function() {
                        Swal.close();
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

    // Solicitar medicamentos
$('#solicitarMedicamentos').on('click', function(e) {
    e.preventDefault();

    // Muestra el loader mientras se procesa la solicitud
    Swal.fire({
        title: 'Procesando solicitud...',
        text: 'Por favor, espera un momento.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    // Realizar la solicitud AJAX al controlador
    $.ajax({
        url: '<?= $this->base_url ?>/medicamentos',
        method: 'GET',
        success: function(response) {
            Swal.close(); // Cerrar el loader al finalizar la solicitud
            if (response.status === 'error') {
                Swal.fire({
                    title: 'No tienes medicamentos pendientes',
                    text: response.message,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            } else if (response.status === 'success') {
                Swal.fire({
                    title: '¡Solicitud enviada!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function() {
            Swal.close(); // Cerrar el loader al finalizar la solicitud
            Swal.fire({
                title: 'Error',
                text: 'No se pudo procesar la solicitud. Inténtalo nuevamente.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});

});
</script>

