<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-gift-fill"></i></div>
        <div class="page-header-text">
            <h1>Dons reçus</h1>
            <p>Gestion des dons collectés</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/dons/nouveau" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Ajouter dons
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
    <div class="table-toolbar">
        <form method="GET" action="<?= BASE_URL ?>/dons" class="table-search-form" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div class="table-search" style="flex: 1; min-width: 200px;">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Rechercher un don…" value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <div style="display: flex; gap: 8px; align-items: center;">
                <label style="font-size: 0.85rem; color: var(--color-text-secondary);">Du:</label>
                <input type="date" name="date_debut" class="form-input" style="padding: 6px 8px; font-size: 0.85rem; width: 140px;" value="<?= htmlspecialchars($date_debut ?? '') ?>">
                <label style="font-size: 0.85rem; color: var(--color-text-secondary);">Au:</label>
                <input type="date" name="date_fin" class="form-input" style="padding: 6px 8px; font-size: 0.85rem; width: 140px;" value="<?= htmlspecialchars($date_fin ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-sm btn-outline">Filtrer</button>
            <?php if (!empty($search) || !empty($date_debut) || !empty($date_fin)): ?>
            <a href="<?= BASE_URL ?>/dons" class="btn btn-sm btn-outline">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dons)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: var(--color-text-secondary);">
                            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 12px;"></i>
                            Aucun don enregistré
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($dons as $don): ?>
                    <tr>
                        <td><?= $don['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($don['date_don'])) ?></td>
                        <td><?= htmlspecialchars($don['details'] ?? 'Aucun détail') ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
