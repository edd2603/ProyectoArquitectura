<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico de Enfermedades</title>
    <!-- Incluye jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Incluye el CSS de FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css" rel="stylesheet">
    
    <!-- Incluye FullCalendar después de jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    
    <!-- Incluye SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    <link rel="stylesheet" href="<?= $this->base_url ?>/assets/css/styles.css"> <!-- Ruta absoluta a los estilos -->

</head>
<body>
   
<header>
    <h1>Diagnóstico de Enfermedades</h1>
</header>

<div>
    <?php echo $content; ?>
</div>

<script>
    // Función para validar la seguridad de la contraseña
    function validarPasswordSegura(password) {
        const minLength = 8;
        const mayuscula = /[A-Z]/;
        const minuscula = /[a-z]/;
        const numero = /[0-9]/;
        const caracterEspecial = /[!@#$%^&*(),.?":{}|<>]/;

        if (password.length < minLength) {
            return 'La contraseña debe tener al menos 8 caracteres.';
        }
        if (!mayuscula.test(password)) {
            return 'La contraseña debe contener al menos una letra mayúscula.';
        }
        if (!minuscula.test(password)) {
            return 'La contraseña debe contener al menos una letra minúscula.';
        }
        if (!numero.test(password)) {
            return 'La contraseña debe contener al menos un número.';
        }
        if (!caracterEspecial.test(password)) {
            return 'La contraseña debe contener al menos un carácter especial.';
        }
        return ''; // Si la contraseña cumple todos los requisitos, no hay mensaje de error
    }

    // Capturar el evento click en el enlace para abrir el formulario de registro con SweetAlert
    document.getElementById('registroLink').addEventListener('click', function(e) {
        e.preventDefault(); // Evitar el comportamiento predeterminado del enlace
        
        Swal.fire({
            title: 'Regístrate',
            html: `
                <form id="registroForm">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" class="swal2-input" required>

                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" class="swal2-input" required>

                    <label for="usuario_registro">Usuario:</label>
                    <input type="text" id="usuario_registro" name="usuario" class="swal2-input" required>

                    <label for="password_registro">Contraseña:</label>
                    <input type="password" id="password_registro" name="password" class="swal2-input" required>
                </form>
            `,
            confirmButtonText: 'Registrarse',
            preConfirm: () => {
                const formData = {
                    nombre: document.getElementById('nombre').value,
                    cedula: document.getElementById('cedula').value,
                    usuario: document.getElementById('usuario_registro').value,
                    password: document.getElementById('password_registro').value
                };

                // Validación de campos vacíos
                if (!formData.nombre || !formData.cedula || !formData.usuario || !formData.password) {
                    Swal.showValidationMessage('Por favor, llena todos los campos');
                    return false;
                }

                // Validar la seguridad de la contraseña
                const passwordValidation = validarPasswordSegura(formData.password);
                if (passwordValidation) {
                    Swal.showValidationMessage(passwordValidation);
                    return false;
                }

                return formData;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar la información del registro mediante AJAX
                $.ajax({
                    url: '<?= $this->base_url ?>/registro', // Ruta al controlador que maneja el registro
                    method: 'POST',
                    data: result.value, // Datos del formulario
                    success: function(response) {
                        // Intentamos analizar la respuesta JSON
                        try {
                            const jsonResponse = typeof response === 'string' ? JSON.parse(response) : response;

                            if (jsonResponse.status === 'error') {
                                // Si hay un error, mostrar la alerta de error
                                Swal.fire({
                                    title: 'Error',
                                    text: jsonResponse.message,
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            } else if (jsonResponse.status === 'success') {
                                // Si el registro es exitoso
                                Swal.fire({
                                    title: 'Registro Exitoso',
                                    text: 'Tu cuenta ha sido creada correctamente.',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.href = '<?= $this->base_url ?>/'; // Redirigir al login
                                });
                            }
                        } catch (e) {
                            console.error('Error al analizar la respuesta JSON:', e);
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error inesperado.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema con la solicitud.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
        });
    });
</script>

</body>
</html>
