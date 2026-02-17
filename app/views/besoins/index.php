<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include __DIR__ . '/../include/header.php'; 
?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <?= htmlspecialchars($_SESSION['error_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill"></i>
        <?= htmlspecialchars($_SESSION['success_message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

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
        <form method="GET" action="<?= BASE_URL ?>/besoins" class="table-search-form" style="display: flex; gap: 10px; align-items: center;">
            <div class="table-search" style="flex: 1;">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Rechercher un besoin…" value="<?= htmlspecialchars($search ?? '') ?>">
            </div>
            <button type="submit" class="btn btn-sm btn-outline">Chercher</button>
            <?php if (!empty($search)): ?>
            <a href="<?= BASE_URL ?>/besoins" class="btn btn-sm btn-outline">Réinitialiser</a>
            <?php endif; ?>
        </form>
    </div>        <div class="table-wrapper">
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
