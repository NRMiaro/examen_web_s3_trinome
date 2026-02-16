<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="page-header-text">
            <h1>Tableau de bord</h1>
            <p>Besoins par ville et dons disponibles</p>
        </div>
    </div>
</div>

<!-- Dons disponibles -->
<div class="stats-grid">
    <?php foreach ($dons_disponibles as $nom => $quantite): 
        $demande = $total_demande[$nom] ?? 0;
        $icon = ($nom === 'Huile' || $nom === 'Eau') ? 'bi-droplet-fill' : 'bi-gift-fill';
        $color = ($nom === 'Huile' || $nom === 'Eau') ? 'blue' : 'green';
        $unite = ($nom === 'Huile' || $nom === 'Eau') ? 'L' : 'kg';
    ?>
    <div class="stat-card">
        <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
        <div class="stat-content">
            <h3><?= htmlspecialchars($nom) ?> disponible</h3>
            <div class="stat-value"><?= number_format($quantite, 0, ',', ' ') ?> <?= $unite ?></div>
            <div class="stat-trend up">Pour <?= number_format($demande, 0, ',', ' ') ?> <?= $unite ?> demandés</div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Villes et besoins -->
<?php foreach ($villes as $ville): ?>
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div>
            <h3><i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($ville['nom']) ?></h3>
            <small class="text-muted"><?= htmlspecialchars($ville['date']) ?></small>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Besoin</th>
                        <th>Quantité demandée</th>
                        <th>Dons disponibles</th>
                        <th>Attribution</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ville['besoins'] as $besoin): 
                        $don_dispo = $dons_disponibles[$besoin['nom']] ?? 0;
                        $attribution = min($besoin['quantite'], $don_dispo);
                        $pourcentage = $besoin['quantite'] > 0 ? round(($attribution / $besoin['quantite']) * 100) : 0;
                        
                        if ($pourcentage >= 100) {
                            $badge = 'badge-success';
                            $statut = '✓ Couvert';
                        } elseif ($pourcentage >= 50) {
                            $badge = 'badge-warning';
                            $statut = '△ Partiel';
                        } else {
                            $badge = 'badge-danger';
                            $statut = '✗ Insuffisant';
                        }
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($besoin['nom']) ?></strong></td>
                        <td><?= number_format($besoin['quantite'], 0, ',', ' ') ?> <?= $besoin['unite'] ?></td>
                        <td><?= number_format($don_dispo, 0, ',', ' ') ?> <?= $besoin['unite'] ?></td>
                        <td>
                            <strong style="color: var(--color-primary);">
                                <?= number_format($attribution, 0, ',', ' ') ?> <?= $besoin['unite'] ?>
                            </strong>
                            <small class="text-muted">(<?= $pourcentage ?>%)</small>
                        </td>
                        <td><span class="badge <?= $badge ?>"><?= $statut ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/include/footer.php'; ?>
