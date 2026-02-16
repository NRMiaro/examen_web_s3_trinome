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

<?php
// Dons disponibles
$dons_disponibles = [
    'Riz' => 2500,
    'Huile' => 1200
];

// Besoins par ville
$villes = [
    [
        'nom' => 'Antananarivo',
        'date' => '16/02/2026',
        'besoins' => [
            ['nom' => 'Riz', 'quantite' => 1000, 'unite' => 'kg'],
            ['nom' => 'Huile', 'quantite' => 500, 'unite' => 'L']
        ]
    ],
    [
        'nom' => 'Mahajanga',
        'date' => '16/02/2026',
        'besoins' => [
            ['nom' => 'Riz', 'quantite' => 2000, 'unite' => 'kg'],
            ['nom' => 'Huile', 'quantite' => 1000, 'unite' => 'L']
        ]
    ]
];
?>

<!-- Dons disponibles -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-gift-fill"></i></div>
        <div class="stat-content">
            <h3>Riz disponible</h3>
            <div class="stat-value">2 500 kg</div>
            <div class="stat-trend up">Pour 3 000 kg demandés</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-droplet-fill"></i></div>
        <div class="stat-content">
            <h3>Huile disponible</h3>
            <div class="stat-value">1 200 L</div>
            <div class="stat-trend up">Pour 1 500 L demandés</div>
        </div>
    </div>
</div>

<!-- Villes et besoins -->
<?php foreach ($villes as $ville): ?>
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <div>
            <h3><i class="bi bi-geo-alt-fill"></i> <?= $ville['nom'] ?></h3>
            <small class="text-muted"><?= $ville['date'] ?></small>
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
                        <td><strong><?= $besoin['nom'] ?></strong></td>
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
