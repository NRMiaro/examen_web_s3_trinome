<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-cart-fill"></i></div>
        <div class="page-header-text">
            <h1>Achats</h1>
            <p>Achats effectués avec les dons en argent</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/achats/nouveau" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Nouvel achat
        </a>
    </div>
</div>

<!-- Solde de la caisse -->
<div class="stats-grid" style="margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-content">
            <h3>Dons en argent</h3>
            <div class="stat-value"><?= number_format($solde['total_dons_argent'] ?? 0, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #fff3e0; color: #e65100;"><i class="bi bi-cart-check"></i></div>
        <div class="stat-content">
            <h3>Total achats</h3>
            <div class="stat-value"><?= number_format($solde['total_achats'] ?? 0, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-wallet2"></i></div>
        <div class="stat-content">
            <h3>Solde disponible</h3>
            <div class="stat-value"><?= number_format($solde['solde'] ?? 0, 0, ',', ' ') ?> Ar</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <!-- Filtre par ville -->
        <div class="table-toolbar">
            <form method="GET" action="<?= BASE_URL ?>/achats" class="table-search-form" style="display: flex; gap: 10px; align-items: center;">
                <label style="font-size: 0.85rem; color: var(--color-text-secondary);">Filtrer par ville:</label>
                <select name="id_ville" class="form-select" style="width: 200px; padding: 6px 8px; font-size: 0.85rem;">
                    <option value="">-- Toutes les villes --</option>
                    <?php foreach ($villes as $ville): ?>
                    <option value="<?= $ville['id'] ?>" <?= ($id_ville ?? '') == $ville['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ville['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-outline">Filtrer</button>
                <?php if (!empty($id_ville)): ?>
                <a href="<?= BASE_URL ?>/achats" class="btn btn-sm btn-outline">Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Besoin</th>
                        <th>Ville</th>
                        <th>Qté</th>
                        <th>Prix unit.</th>
                        <th>Frais (%)</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($achats)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--color-text-secondary);">
                            <i class="bi bi-cart-x" style="font-size: 2rem; display: block; margin-bottom: 12px;"></i>
                            Aucun achat enregistré
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($achats as $achat): ?>
                    <tr>
                        <td><?= $achat['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($achat['date_achat'])) ?></td>
                        <td><?= htmlspecialchars($achat['besoin_nom']) ?></td>
                        <td><?= htmlspecialchars($achat['ville_nom']) ?></td>
                        <td><?= number_format($achat['quantite'], 0, ',', ' ') ?></td>
                        <td><?= number_format($achat['prix_unitaire'], 0, ',', ' ') ?> Ar</td>
                        <td><?= $achat['frais_pourcent'] ?>%</td>
                        <td><strong><?= number_format($achat['total'], 0, ',', ' ') ?> Ar</strong></td>
                        <td>
                            <form method="POST" action="<?= BASE_URL ?>/achats/<?= $achat['id'] ?>/supprimer" style="display: inline;" class="form-delete-achat">
                                <button type="submit" class="btn btn-icon btn-outline btn-sm" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>

<script nonce="<?= Flight::app()->get('csp_nonce') ?>">
document.querySelectorAll('.form-delete-achat').forEach(function(form) {
    form.addEventListener('submit', function(e) {
        if (!confirm('Supprimer cet achat ?')) {
            e.preventDefault();
        }
    });
});
</script>
