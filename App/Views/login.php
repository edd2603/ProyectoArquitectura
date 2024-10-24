<?php
    $config = include(__DIR__ . '/../../config/config.php');
    $base_url = $config['base_url'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <!-- Usar la variable base_url para cargar el archivo CSS correctamente -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/styles.css">
    <link rel="stylesheet" href="<?= $this->base_url ?>/assets/css/perfil.css">

</head>
<body>
    <div class="container">
        <img src="<?php echo $base_url; ?>/assets/images/logo_medico.png" alt="Logo">

        <h2>Iniciar Sesión</h2>
        <form action="<?php echo $base_url; ?>/login" method="POST" id="loginForm">
            <label for="usuario_login">Usuario:</label>
            <input type="text" id="usuario_login" name="usuario" placeholder="Usuario" required>

            <label for="password_login">Contraseña:</label>
            <input type="password" id="password_login" name="password" placeholder="Contraseña" required>

            <button type="submit">Iniciar Sesión</button>
        </form>

        <p class="message">¿No tienes cuenta? <a href="#" id="registroLink">Regístrate</a></p>
    </div>
</body>
</html>
