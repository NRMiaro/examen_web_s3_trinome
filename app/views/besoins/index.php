<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-clipboard-check"></i></div>
        <div class="page-header-text">
            <h1>Besoins</h1>
            <p>Gestion des besoins (Riz, Huile, etc.)</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="<?= BASE_URL ?>/besoins/creer" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter un besoin
        </a>
        <a href="<?= BASE_URL ?>/besoins/nouveau" class="btn btn-secondary">
            <i class="bi bi-clipboard-plus"></i> Nouvelle demande
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-toolbar">
            <div class="table-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Rechercher un besoin…">
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Prix unitaire</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($besoins)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 40px; color: var(--color-text-secondary);">
                            <i class="bi bi-inbox" style="font-size: 2rem; display: block; margin-bottom: 12px;"></i>
                            Aucun besoin enregistré
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($besoins as $besoin): ?>
                    <tr>
                        <td><?= $besoin['id'] ?></td>
                        <td><strong><?= htmlspecialchars($besoin['nom']) ?></strong></td>
                        <td><span class="badge badge-neutral"><?= ucfirst(htmlspecialchars($besoin['type_nom'])) ?></span></td>
                        <td><?= number_format($besoin['prix'], 0, ',', ' ') ?> Ar</td>
                        <td>
                            <div class="actions">
                                <a href="<?= BASE_URL ?>/besoins/<?= $besoin['id'] ?>/modifier" class="btn btn-icon btn-outline btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="<?= BASE_URL ?>/besoins/<?= $besoin['id'] ?>/supprimer" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce besoin ?');">
                                    <button type="submit" class="btn btn-icon btn-outline btn-sm" title="Supprimer"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
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
