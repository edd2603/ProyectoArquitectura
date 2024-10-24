<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <title>Calendario Médico</title>
</head>
<body>

<div id="calendar"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var medico_id = <?= $medico_id ?>; // Pasar dinámicamente el ID del médico

        // Inicializar el calendario con eventos dinámicos
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            events: function(fetchInfo, successCallback, failureCallback) {
                // Llamada AJAX para obtener los eventos
                $.ajax({
                    url: '<?= $this->base_url ?>/citas/obtenerEventos/' + medico_id,
                    method: 'GET',
                    success: function(response) {
                        successCallback(response);  // Añadir eventos al calendario
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            selectable: true, // Permitir la selección de fechas
            select: function(info) {
                var fecha = info.startStr;

                Swal.fire({
                    title: '¿Confirmar cita?',
                    text: "Has seleccionado: " + fecha,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Confirmar la cita vía AJAX
                        $.ajax({
                            url: '<?= $this->base_url ?>/citas/confirmar',
                            method: 'POST',
                            data: {
                                medico_id: medico_id,
                                fecha: fecha
                            },
                            success: function(response) {
                                Swal.fire('Cita confirmada', 'Tu cita ha sido confirmada', 'success');
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo confirmar la cita', 'error');
                            }
                        });
                    }
                });
            }
        });

        calendar.render(); // Renderizar el calendario
    });
</script>

</body>
</html>
