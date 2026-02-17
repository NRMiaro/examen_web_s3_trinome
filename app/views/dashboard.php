<?php include __DIR__ . '/include/header.php'; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'dispatch_valide'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-bottom: 20px;">
        <i class="bi bi-check-circle-fill"></i>
        <strong>Succès :</strong> Le dispatch a été validé avec succès.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="page-header">   
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-geo-alt-fill"></i></div>
        <div class="page-header-text">
            <h1>Tableau de bord</h1>
            <p>Besoins par ville et dons disponibles</p>
        </div>
    </div>
</div>

<!-- Dons disponibles (affiché seulement si au moins un besoin 100% couvert) -->
<?php if (!empty($besoinsVilles)): ?>
<div class="stats-grid">
    <?php foreach ($dons_restants as $nom => $quantite): 
        $quantiteInitiale = $dons_disponibles[$nom] ?? 0;
        $demande = $total_demande[$nom] ?? 0;
        $icon = ($nom === 'Argent') ? 'bi-cash-coin' : (($nom === 'Huile' || $nom === 'Eau') ? 'bi-droplet-fill' : 'bi-gift-fill');
        $color = ($nom === 'Argent') ? 'orange' : (($nom === 'Huile' || $nom === 'Eau') ? 'blue' : 'green');
        $unite = \app\models\DashboardModel::getUnite($nom);
    ?>
    <div class="stat-card">
        <div class="stat-icon <?= $color ?>"><i class="bi <?= $icon ?>"></i></div>
        <div class="stat-content">
            <h3><?= htmlspecialchars($nom) ?> restant</h3>
            <div class="stat-value"><?= number_format($quantite, 0, ',', ' ') ?> <?= $unite ?></div>
            <div class="stat-trend up">
                <?php if ($quantiteInitiale > 0): ?>
                    Réparti: <?= number_format($quantiteInitiale - $quantite, 0, ',', ' ') ?> / <?= number_format($quantiteInitiale, 0, ',', ' ') ?> <?= $unite ?>
                <?php else: ?>
                    Aucun don disponible
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Demandes par ville (uniquement 100% couvertes) -->
<?php if (empty($besoinsVilles)): ?>
    <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 12px; margin-top: 20px;">
        <i class="bi bi-inbox" style="font-size: 3em; color: #adb5bd;"></i>
        <h3 style="margin-top: 16px; color: #333;">Aucun besoin entièrement couvert</h3>
        <p style="color: #666;">Les besoins apparaîtront ici une fois couverts à 100% (dons validés + achats).<br>Rendez-vous sur la page <a href="<?= BASE_URL ?>/simulation">Simulation</a> pour simuler et valider le dispatch.</p>
    </div>
<?php endif; ?>
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
            <?php
                // Créer la clé unique pour ce produit
                $cle = $demande['id_besoin_ville'] . '_' . $produit['id_besoin'];
                $dispatchStatus = $dispatch[$cle] ?? null;
                $alloue = (int) ($dispatchStatus['alloue'] ?? 0);
                $achete = (int) ($dispatchStatus['achete'] ?? 0);
            ?>
        <div class="card" style="margin-bottom: 12px;">
            <div class="card-header">
                <div>
                    <h4 style="margin: 0;"><?= htmlspecialchars($produit['nom']) ?></h4>
                    <small class="text-muted">Type: <?= htmlspecialchars($produit['type']) ?></small>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">DEMANDÉ</strong>
                        <p style="font-size: 1.2em; margin: 8px 0 0 0; color: var(--color-primary);">
                            <?= number_format($produit['quantite'], 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">ALLOUÉ (dons)</strong>
                        <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #4CAF50;">
                            <?= number_format($alloue, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">ACHETÉ</strong>
                        <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #FF9800;">
                            <?= number_format($achete, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">STATUT</strong>
                        <p style="margin: 8px 0 0 0;">
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 0.85em; font-weight: 600; background-color: #4CAF5020; color: #4CAF50; border-left: 3px solid #4CAF50;">
                                <i class="bi bi-check-circle-fill"></i> Complété
                            </span>
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
