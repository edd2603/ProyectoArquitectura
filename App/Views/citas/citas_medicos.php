<div class="citas-medicos-container">
    <h2>Médicos disponibles para <?= $tipo ?></h2>
    <ul>
        <?php foreach ($medicos as $medico): ?>
            <li>
                <a href="<?= $this->base_url ?>/citas/calendario/<?= $medico['id'] ?>">
                    Dr. <?= $medico['nombre'] ?> - <?= $medico['especialidad'] ?? 'General' ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
