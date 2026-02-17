<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-cash-coin"></i></div>
        <div class="page-header-text">
            <h1>Caisse</h1>
            <p>Gestion des dons d'argent</p>
        </div>
    </div>

    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/dons/nouveau" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Ajouter de l'argent
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; border-radius: 8px; margin-bottom: 20px; color: white;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="margin: 0; font-size: 14px; opacity: 0.9;">Solde total</p>
                    <h2 style="margin: 10px 0 0 0; font-size: 32px; font-weight: bold;">
                       <?= number_format($total, 0, ',', ' ') ?> Ar
                    </h2>
                </div>
                <i class="bi bi-wallet2" style="font-size: 48px; opacity: 0.3;"></i>
            </div>
        </div>

        <div class="table-toolbar">
            <form method="GET" action="<?= BASE_URL ?>/caisse" class="table-search-form" style="display: flex; gap: 10px; align-items: center;">
                <div class="table-search" style="flex: 1;">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" placeholder="Rechercher…" value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-sm btn-outline">Chercher</button>
                <?php if (!empty($search)): ?>
                <a href="<?= BASE_URL ?>/caisse" class="btn btn-sm btn-outline">Réinitialiser</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($caisses)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px; color: var(--color-text-secondary);">
                            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 12px;"></i>
                            Aucun don d'argent enregistré
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($caisses as $caisse): ?>
                    <tr>
                        <td><?= $caisse['id'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($caisse['date_don'])) ?></td>
                        <td><strong><?= number_format($caisse['montant'], 0, ',', ' ') ?> Ar</strong></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
