<?php

namespace App\Models;

class Paciente {
    public $nombre;
    public $cedula;
    public $usuario;
    public $password;

    public function __construct($nombre, $cedula, $usuario, $password) {
        $this->nombre = $nombre;
        $this->cedula = $cedula;
        $this->usuario = $usuario;
        $this->password = $password;
    }
}
