<?php
error_log("Esto es un mensaje de prueba en el log de errores.");
trigger_error("Error de prueba en el log", E_USER_ERROR);
