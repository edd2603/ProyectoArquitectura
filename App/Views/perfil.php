<div class="perfil-container">
    <div class="header-perfil">
        <h1>Bienvenido, <?= $paciente['nombre'] ?></h1>
        <p>Rol: paciente</p>
    </div>

    <div class="perfil-content">
        <div class="perfil-foto">
            <img src="<?= $paciente['foto'] ? $this->base_url . '/' . $paciente['foto'] : 'assets/images/default-avatar.png' ?>" alt="Foto de perfil" class="foto-perfil">
        </div>

        <div class="perfil-info">
            <h2>Perfil de <?= $paciente['nombre'] ?></h2>
            <p><strong>Email:</strong> <?= $paciente['correo'] ?></p>

            <!-- Formulario unificado -->
            <form action="<?= $this->base_url ?>/perfil" method="POST" enctype="multipart/form-data" class="perfil-form">
                <div class="form-group">
                    <label for="nombre">Nombre:</label>
                    <input type="text" name="nombre" id="nombre" value="<?= $paciente['nombre'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="correo">Email:</label>
                    <input type="email" name="correo" id="correo" value="<?= $paciente['correo'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" name="telefono" id="telefono" value="<?= $paciente['telefono'] ?>" required>
                </div>

                <div class="form-group">
                    <label for="password">Nueva Contraseña (Opcional):</label>
                    <input type="password" name="password" id="password">
                </div>

                <div class="form-group">
                    <label for="foto">Actualizar Foto de Perfil:</label>
                    <input type="file" name="foto" id="foto">
                </div>

                <button type="submit" class="btn-actualizar">Actualizar Datos</button>
            </form>
        </div>
    </div>
</div>
