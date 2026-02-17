<?php include __DIR__ . '/../include/header.php'; ?>

<?php if (isset($_GET['success']) && $_GET['success'] === 'dispatch_valide'): ?>
    <div style="background: #d4edda; border: 1px solid #c3e6cb; border-left: 4px solid #28a745; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #155724;">
        <i class="bi bi-check-circle-fill"></i>
        <strong>Succès !</strong> Le dispatch a été validé. Cliquez sur <strong>"Actualiser la simulation"</strong> pour recalculer avec les besoins restants.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'validation_failed'): ?>
    <div style="background: #fdecea; border: 1px solid #f5c6cb; border-left: 4px solid #dc3545; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #721c24;">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <strong>Erreur :</strong> La validation du dispatch a échoué. Veuillez réessayer.
    </div>
<?php endif; ?>

<div class="page-header">   
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-clipboard-data"></i></div>
        <div class="page-header-text">
            <h1>Simulation de Dispatch</h1>
            <p>Prévisualisation de l'allocation des dons avant validation</p>
        </div>
    </div>
    <div class="page-header-right" style="display: flex; gap: 10px; align-items: center;">
        <a href="<?= BASE_URL ?>/simulation" class="btn" style="padding: 10px 20px; font-size: 1em; border-radius: 6px; background-color: #007bff; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; text-decoration: none;">
            <i class="bi bi-arrow-clockwise"></i>
            Actualiser la simulation
        </a>
        <?php if (!empty($besoinsVilles)): ?>
        <form method="POST" action="<?= BASE_URL ?>/simulation/valider" style="display: inline;">
            <button type="submit" class="btn btn-success" style="padding: 10px 20px; font-size: 1em; border-radius: 6px; background-color: #28a745; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle"></i>
                Valider le Dispatch
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- Dons disponibles -->
<div class="stats-grid">
    <?php foreach ($dons_restants as $nom => $quantite): 
        $quantiteInitiale = $dons_disponibles[$nom] ?? 0;
        $demande = $total_demande[$nom] ?? 0;
        $icon = ($nom === 'Huile' || $nom === 'Eau') ? 'bi-droplet-fill' : 'bi-gift-fill';
        $color = ($nom === 'Huile' || $nom === 'Eau') ? 'blue' : 'green';
        $unite = ($nom === 'Huile' || $nom === 'Eau') ? 'L' : 'kg';
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

<!-- Demandes par ville -->
<?php if (empty($besoinsVilles)): ?>
    <div style="text-align: center; padding: 60px 20px; background: #f8f9fa; border-radius: 12px; margin-top: 20px;">
        <i class="bi bi-check-circle-fill" style="font-size: 3em; color: #28a745;"></i>
        <h3 style="margin-top: 16px; color: #333;">Toutes les demandes ont été validées !</h3>
        <p style="color: #666;">Il n'y a aucune demande en attente de dispatch. Consultez le <a href="<?= BASE_URL ?>/">tableau de bord</a> pour voir les résultats.</p>
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
                // Clé unique pour ce produit
                $cle = $demande['id_besoin_ville'] . '_' . $produit['id_besoin'];
                
                // Couverture actuelle réelle (validé + acheté)
                $cov = $coverage[$cle] ?? null;
                $dejaAlloue = (int) ($cov['alloue'] ?? 0);
                $dejaAchete = (int) ($cov['achete'] ?? 0);
                $dejaCouvert = $dejaAlloue + $dejaAchete;
                $resteManquant = max(0, $produit['quantite'] - $dejaCouvert);
                $pourcentageCouvert = $produit['quantite'] > 0 
                    ? min(100, round(($dejaCouvert / $produit['quantite']) * 100)) 
                    : 0;
                
                // Simulation théorique (ce que les dons restants pourraient couvrir)
                $simStatus = $dispatch[$cle] ?? null;
                $simAlloue = (int) ($simStatus['alloue'] ?? 0);
                
                // Statut global
                if ($dejaCouvert === 0 && $simAlloue === 0) {
                    $statusColor = '#F44336'; $statusText = 'Non couvert';
                } elseif ($resteManquant > 0 && $simAlloue > 0) {
                    $statusColor = '#FF9800'; $statusText = 'Partiel';
                } elseif ($resteManquant > 0) {
                    $statusColor = '#F44336'; $statusText = 'Insuffisant';
                } else {
                    $statusColor = '#4CAF50'; $statusText = 'Couvert par simulation';
                }
            ?>
        <div class="card" style="margin-bottom: 12px;">
            <div class="card-header">
                <div>
                    <h4 style="margin: 0;"><?= htmlspecialchars($produit['nom']) ?></h4>
                    <small class="text-muted">Type: <?= htmlspecialchars($produit['type']) ?></small>
                </div>
                <div>
                    <span style="display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 0.85em; font-weight: 600; background-color: <?= $statusColor ?>20; color: <?= $statusColor ?>; border-left: 3px solid <?= $statusColor ?>;">
                        <?= $statusText ?> (<?= $pourcentageCouvert ?>%)
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr 1fr; gap: 12px;">
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">DEMANDÉ</strong>
                        <p style="font-size: 1.1em; margin: 6px 0 0 0; color: var(--color-primary);">
                            <?= number_format($produit['quantite'], 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">VALIDÉ (dons)</strong>
                        <p style="font-size: 1.1em; margin: 6px 0 0 0; color: #4CAF50;">
                            <?= number_format($dejaAlloue, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">ACHETÉ</strong>
                        <p style="font-size: 1.1em; margin: 6px 0 0 0; color: #FF9800;">
                            <?= number_format($dejaAchete, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">RESTE</strong>
                        <p style="font-size: 1.1em; margin: 6px 0 0 0; color: #F44336; font-weight: 600;">
                            <?= number_format($resteManquant, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">SIMULATION</strong>
                        <p style="font-size: 1.1em; margin: 6px 0 0 0; color: #2196F3;">
                            <?php if ($simAlloue > 0): ?>
                                +<?= number_format($simAlloue, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                            <?php else: ?>
                                <span style="color: #999;">—</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <!-- Barre de progression -->
                <div style="margin-top: 12px; background: #e9ecef; border-radius: 4px; height: 8px; overflow: hidden;">
                    <?php $pctValide = $produit['quantite'] > 0 ? min(100, round(($dejaAlloue / $produit['quantite']) * 100)) : 0; ?>
                    <?php $pctAchete = $produit['quantite'] > 0 ? min(100 - $pctValide, round(($dejaAchete / $produit['quantite']) * 100)) : 0; ?>
                    <?php $pctSim = $produit['quantite'] > 0 ? min(100 - $pctValide - $pctAchete, round(($simAlloue / $produit['quantite']) * 100)) : 0; ?>
                    <div style="display: flex; height: 100%;">
                        <div style="width: <?= $pctValide ?>%; background: #4CAF50;" title="Validé"></div>
                        <div style="width: <?= $pctAchete ?>%; background: #FF9800;" title="Acheté"></div>
                        <div style="width: <?= $pctSim ?>%; background: #2196F3; opacity: 0.6;" title="Simulation"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endforeach; ?>

<?php include __DIR__ . '/../include/footer.php'; ?>
