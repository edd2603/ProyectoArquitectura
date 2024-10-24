<?php

namespace Core;

class Database {
    private $mysqli;

    public function __construct($config) {
        // Conectar usando MySQLi
        $this->mysqli = new \mysqli($config['db_host'], $config['db_user'], $config['db_pass'], $config['db_name']);

        // Verificar conexión
        if ($this->mysqli->connect_error) {
            die("Error de conexión: " . $this->mysqli->connect_error);
        }
    }

    // Método para ejecutar consultas SQL con parámetros
    public function query($sql, $params = []) {
        $stmt = $this->mysqli->prepare($sql);
        if ($stmt === false) {
            die('Error en la consulta: ' . $this->mysqli->error);
        }

        // Si hay parámetros, los vinculamos a la consulta
        if (!empty($params)) {
            // Definir tipos de datos de los parámetros (ejemplo: "s" para strings, "i" para enteros)
            $types = str_repeat('s', count($params)); // Aquí todos se marcan como 's' para strings
            $stmt->bind_param($types, ...$params);
        }

        // Ejecutar la consulta
        $stmt->execute();

        return $stmt;
    }

    // Obtener todos los resultados como un array asociativo
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // Obtener un solo resultado como un array asociativo
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Ejecutar una consulta que no devuelva resultados (INSERT, UPDATE, DELETE)
    public function execute($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->affected_rows;
    }

    // Cerrar la conexión cuando ya no se necesite
    public function close() {
        $this->mysqli->close();
    }
}
