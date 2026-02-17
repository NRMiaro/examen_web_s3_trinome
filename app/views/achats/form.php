<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-cart-plus-fill"></i></div>
        <div class="page-header-text">
            <h1><?= $page_title ?? 'Nouvel achat' ?></h1>
            <p>Acheter des besoins restants avec les dons en argent</p>
        </div>
    </div>
</div>

<!-- Solde disponible -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-wallet2"></i></div>
        <div class="stat-content">
            <h3>Solde caisse disponible</h3>
            <div class="stat-value"><?= number_format($solde['solde'] ?? 0, 0, ',', ' ') ?> Ar</div>
            <div class="stat-trend">Frais d'achat : <?= $frais_pourcent ?>%</div>
        </div>
    </div>
</div>

<?php if (!empty($error)): ?>
<div style="background: #fdecea; border: 1px solid #f5c6cb; border-left: 4px solid #dc3545; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #721c24;">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="form-container">
            <?php if (empty($besoinsRestants)): ?>
            <div style="text-align: center; padding: 40px; color: var(--color-text-secondary);">
                <i class="bi bi-check-circle" style="font-size: 3rem; display: block; margin-bottom: 16px; color: #4CAF50;"></i>
                <h3>Tous les besoins sont couverts !</h3>
                <p>Il n'y a aucun besoin restant à acheter.</p>
                <a href="<?= BASE_URL ?>/achats" class="btn btn-outline" style="margin-top: 16px;">Retour aux achats</a>
            </div>
            <?php else: ?>
            <h4 style="margin: 0 0 20px; font-size: 1rem; font-weight: 600;">Besoins restants (non couverts par les dons)</h4>

            <div class="table-wrapper" style="margin-bottom: 24px;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Besoin</th>
                            <th>Ville</th>
                            <th>Demandé</th>
                            <th>Alloué (dons)</th>
                            <th>Déjà acheté</th>
                            <th>Manquant</th>
                            <th>Prix unit.</th>
                            <th>Coût estimé (+ <?= $frais_pourcent ?>%)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($besoinsRestants as $besoin): ?>
                        <?php
                            $cout_brut = $besoin['manquant'] * $besoin['prix_unitaire'];
                            $cout_total = (int) ceil($cout_brut * (1 + $frais_pourcent / 100));
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($besoin['besoin_nom']) ?></strong><br><small class="text-muted"><?= $besoin['type_nom'] ?></small></td>
                            <td><?= htmlspecialchars($besoin['ville_nom']) ?></td>
                            <td><?= number_format($besoin['quantite_demandee'], 0, ',', ' ') ?></td>
                            <td><?= number_format($besoin['alloue'], 0, ',', ' ') ?></td>
                            <td><?= number_format($besoin['deja_achete'] ?? 0, 0, ',', ' ') ?></td>
                            <td><strong style="color: #dc3545;"><?= number_format($besoin['manquant'], 0, ',', ' ') ?></strong></td>
                            <td><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                            <td><strong><?= number_format($cout_total, 0, ',', ' ') ?> Ar</strong></td>
                            <td>
                                <form method="POST" action="<?= BASE_URL ?>/achats" style="display: flex; gap: 6px; align-items: center;">
                                    <input type="hidden" name="id_besoin" value="<?= $besoin['id_besoin'] ?>">
                                    <input type="hidden" name="id_ville" value="<?= $besoin['id_ville'] ?>">
                                    <input type="hidden" name="prix_unitaire" value="<?= $besoin['prix_unitaire'] ?>">
                                    <input type="number" name="quantite" value="<?= $besoin['manquant'] ?>" min="1" max="<?= $besoin['manquant'] ?>" class="form-input" style="width: 80px; padding: 4px 8px; font-size: 0.85rem;">
                                    <button type="submit" class="btn btn-sm btn-success btn-achat-confirm" title="Acheter">
                                        <i class="bi bi-cart-check"></i> Acheter
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <a href="<?= BASE_URL ?>/achats" class="btn btn-outline">Retour aux achats</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script nonce="<?= Flight::app()->get('csp_nonce') ?>">
document.querySelectorAll('.btn-achat-confirm').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        if (!confirm('Confirmer l\'achat ?')) {
            e.preventDefault();
        }
    });
});
</script>
