<?php
    $config = include(__DIR__ . '/../../config/config.php');
    $base_url = $config['base_url'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Exitoso</title>
</head>
<body>
    <h1>¡Registro exitoso!</h1>
    <p>Gracias por registrarte. Ahora puedes <a href="<?php echo $base_url; ?>/">iniciar sesión</a>.</p>
</body>
</html>
