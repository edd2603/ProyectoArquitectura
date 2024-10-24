<?php
session_start();
$paciente_id = $_SESSION['id'];  // Asegurarnos de que el paciente está logueado y el ID está disponible
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="<?= $this->base_url ?>/assets/css/calendario.css">
    <title>Solicitar Cita Médica</title>
    <style>
        /* Estilo para las franjas horarias deshabilitadas */
        .fc-disabled-slot {
            background-color: #d3d3d3 !important; /* Cambia a gris para indicar que está deshabilitado */
            pointer-events: none; /* Deshabilita la interacción */
            opacity: 0.6;
        }
    </style>
</head>
<body>

<h2 class="titulo-citas">Solicitar Cita Médica</h2>

<div class="citas-container">
    <!-- Agregar la selección de citas -->
    <div class="cita-card">
        <i class="fas fa-user-md"></i>
        <h3>Cita médica presencial</h3>
        <p>Consulta con un médico general de forma presencial.</p>
        <a href="#" class="btn-cita cita-link" data-tipo="General">Seleccionar</a>
    </div>

    <div class="cita-card">
        <i class="fas fa-laptop-medical"></i>
        <h3>Cita médica virtual</h3>
        <p>Consulta a través de videollamada con un médico general.</p>
        <a href="#" class="btn-cita cita-link" data-tipo="General">Seleccionar</a>
    </div>

    <div class="cita-card">
        <i class="fas fa-stethoscope"></i>
        <h3>Cita con especialista</h3>
        <p>Consulta con un médico especialista en la rama que elijas.</p>
        <a href="#" class="btn-cita cita-link" data-tipo="Especialista">Seleccionar</a>
    </div>
</div>

<div id="calendar-container"></div>

<script>
    var paciente_id = <?= $paciente_id ?>;  // Aseguramos que el paciente_id esté disponible en JavaScript

    $('.cita-link').on('click', function(e) {
        e.preventDefault();
        var tipo = $(this).data('tipo');

        $.ajax({
            url: '<?= $this->base_url ?>/citas/medicos/' + tipo,
            method: 'GET',
            success: function(response) {
                var html = '<select id="medico-select">';
                response.forEach(function(medico) {
                    html += '<option value="' + medico.id + '">' + medico.nombre + ' (' + medico.especialidad + ')</option>';
                });
                html += '</select>';

                Swal.fire({
                    title: 'Selecciona un Médico',
                    html: html,
                    confirmButtonText: 'Ver Calendario',
                    showCancelButton: true,
                    preConfirm: () => {
                        var medico_id = $('#medico-select').val();
                        return medico_id;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        cargarCalendario(result.value);
                    }
                });
            },
            error: function() {
                Swal.fire('Error', 'Error al cargar los médicos.', 'error');
            }
        });
    });

    function cargarCalendario(medico_id) {
        $.ajax({
            url: '<?= $this->base_url ?>/citas/calendario/' + medico_id,
            method: 'GET',
            success: function(response) {
                $('#calendar-container').html(response);
                inicializarCalendario(medico_id);
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar el calendario.', 'error');
            }
        });
    }

    function inicializarCalendario(medico_id) {
        var calendarEl = document.getElementById('calendar');

        if (!calendarEl) {
            console.error('No se encontró el contenedor del calendario.');
            return;
        }

        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'es',
            initialView: 'timeGridWeek',
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6], // Lunes a sábado
                startTime: '07:00',
                endTime: '19:00',
            },
            validRange: {
                start: new Date().toISOString().split('T')[0], // No permitir seleccionar días anteriores a hoy
            },
            allDaySlot: false,
            slotMinTime: '07:00:00',
            slotMaxTime: '19:00:00',
            selectable: true,
            events: function(fetchInfo, successCallback, failureCallback) {
                $.ajax({
                    url: '<?= $this->base_url ?>/citas/obtenerEventos/' + medico_id,
                    method: 'GET',
                    success: function(response) {
                        successCallback(response);
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            select: function(info) {
                var fecha = info.startStr;
                var now = new Date();

                // Deshabilitar horas anteriores en el día actual
                if (new Date(fecha).getTime() < now.getTime()) {
                    Swal.fire('Hora no válida', 'No se puede seleccionar una hora que ya ha pasado.', 'error');
                    return;
                }

Swal.fire({
    title: '¿Confirmar cita?',
    text: "Has seleccionado: " + new Date(fecha).toLocaleString('es-ES', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }),  // Mostrar solo la fecha y la hora seleccionada
    icon: 'info',
    showCancelButton: true,
    confirmButtonText: 'Confirmar',
    cancelButtonText: 'Cancelar'
})
.then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= $this->base_url ?>/citas/confirmar',
                            method: 'POST',
                            data: {
                                medico_id: medico_id,
                                fecha: fecha,
                                paciente_id: paciente_id  // Enviamos el paciente_id correctamente
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        title: 'Cita confirmada',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'OK'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            inicializarCalendario(medico_id); // Recargar el calendario
                                        }
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire('Error', 'No se pudo confirmar la cita.', 'error');
                            }
                        });
                    }
                });
            },
            eventClick: function(info) {
                var citaId = info.event.id;
                Swal.fire({
                    title: '¿Cancelar cita?',
                    text: "¿Estás seguro de cancelar esta cita?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cancelar',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '<?= $this->base_url ?>/citas/cancelar',
                            method: 'POST',
                            data: {
                                cita_id: citaId
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire('Cancelado', response.message, 'success').then(() => {
                                        inicializarCalendario(medico_id); // Recargar el calendario
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error', 'No se pudo cancelar la cita.', 'error');
                            }
                        });
                    }
                });
            },
            viewDidMount: function() {
                marcarHorasPasadas(); // Llamar la función para marcar las horas pasadas
            }
        });

        calendar.render();
    }

    // Función para marcar las horas pasadas y cambiar su color
    function marcarHorasPasadas() {
        var now = new Date();
        var currentDate = now.toISOString().split('T')[0];
        var currentTime = now.getHours() * 60 + now.getMinutes();

        // Si es el día actual, aplicar los estilos
        if (currentDate === document.querySelector('.fc-day-today').getAttribute('data-date')) {
            document.querySelectorAll('.fc-timegrid-slot-lane').forEach(function(slot) {
                var timeText = slot.getAttribute('data-time');
                if (timeText) {
                    var timeParts = timeText.split(':');
                    var slotMinutes = parseInt(timeParts[0]) * 60 + parseInt(timeParts[1]);

                    // Si el slot está en el pasado, aplicar el estilo
                    if (slotMinutes < currentTime) {
                        slot.classList.add('fc-disabled-slot');
                    }
                }
            });
        }
    }
</script>

</body>
</html>
