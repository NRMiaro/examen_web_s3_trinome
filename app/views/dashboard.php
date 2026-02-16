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
                
                // Récupérer le statut de dispatch pour ce produit spécifique
                $dispatchStatus = $dispatch[$cle] ?? null;
                $statut = $dispatchStatus ? $dispatchStatus['statut'] : 'unknown';
                $alloue = $dispatchStatus ? $dispatchStatus['alloue'] : 0;
                $manquant = $dispatchStatus ? $dispatchStatus['manquant'] : $produit['quantite'];
                $pourcentage = $dispatchStatus ? $dispatchStatus['pourcentage'] : 0;
            ?>
        <div class="card" style="margin-bottom: 12px;">
            <div class="card-header">
                <div>
                    <h4 style="margin: 0;"><?= htmlspecialchars($produit['nom']) ?></h4>
                    <small class="text-muted">Type: <?= htmlspecialchars($produit['type']) ?></small>
                </div>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">DEMANDÉ</strong>
                        <p style="font-size: 1.2em; margin: 8px 0 0 0; color: var(--color-primary);">
                            <?= number_format($produit['quantite'], 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">ALLOUÉ</strong>
                        <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #4CAF50;">
                            <?= number_format($alloue, 0, ',', ' ') ?> <span style="font-size: 0.8em; color: #999;"><?= $produit['unite'] ?></span>
                        </p>
                    </div>
                    <div>
                        <strong style="font-size: 0.85em; color: #666;">STATUT</strong>
                        <p style="margin: 8px 0 0 0;">
                            <?php
                            $statusColor = '#4CAF50';
                            $statusText = 'Complété';
                            if ($statut === 'partial') {
                                $statusColor = '#FF9800';
                                $statusText = 'Partiel';
                            } elseif ($statut === 'unresolved') {
                                $statusColor = '#F44336';
                                $statusText = 'Invalide';
                            }
                            ?>
                            <span style="display: inline-block; padding: 4px 10px; border-radius: 3px; font-size: 0.85em; font-weight: 600; background-color: <?= $statusColor ?>20; color: <?= $statusColor ?>; border-left: 3px solid <?= $statusColor ?>;">
                                <?= $statusText ?>
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
