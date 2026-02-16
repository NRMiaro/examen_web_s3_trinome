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

<!-- Demandes par ville -->
<?php foreach ($besoinsVilles as $villeData): ?>
<div style="margin-bottom: 40px;">
    <h2 style="margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid var(--color-primary);">
        <i class="bi bi-geo-alt-fill"></i> <?= htmlspecialchars($villeData['nom']) ?>
    </h2>
    
    <?php foreach ($villeData['demandes'] as $demande): ?>
    <div style="margin-left: 16px; margin-bottom: 24px; padding-left: 16px; border-left: 3px solid #ddd;">
        <h3 style="margin-bottom: 16px; font-size: 1.1em;">
            <i class="bi bi-calendar-event"></i> Demande du <?= htmlspecialchars($demande['date']) ?>
        </h3>
        
        <?php foreach ($demande['produits'] as $produit): ?>
        <div class="card" style="margin-bottom: 12px;">
            <div class="card-header">
                <div>
                    <h4 style="margin: 0;"><?= htmlspecialchars($produit['nom']) ?></h4>
                    <small class="text-muted">Type: <?= htmlspecialchars($produit['type']) ?></small>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px;">
                    <div>
                        <strong>Quantité</strong>
                        <p style="font-size: 1.3em; margin: 8px 0 0 0; color: var(--color-primary);">
                            <?= number_format($produit['quantite'], 0, ',', ' ') ?> <span style="font-size: 0.75em;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong>Disponible</strong>
                        <p style="font-size: 1.3em; margin: 8px 0 0 0;">
                            <?= number_format($dons_disponibles[$produit['nom']] ?? 0, 0, ',', ' ') ?> <span style="font-size: 0.75em;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/include/footer.php'; ?>
