<h2>Médicos Disponibles para Cita <?= ucfirst($tipo) ?></h2>

<ul class="lista-medicos">
    <?php foreach ($medicos as $medico): ?>
        <li>
            <strong><?= $medico['nombre'] ?></strong> - <?= $medico['especialidad'] ?>
            <a href="<?= $this->base_url ?>/citas/calendario/<?= $medico['id'] ?>" class="btn-calendario">Ver Calendario</a>
        </li>
    <?php endforeach; ?>
</ul>
