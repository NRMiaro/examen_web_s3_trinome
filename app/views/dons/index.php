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
        <a href="/dons/nouveau" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Nouveau don
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-toolbar">
            <div class="table-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Rechercher un don…">
            </div>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Détails</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $dons = [
                        [1, '16/02/2026', 'Riz: 2500 kg, Huile: 1200 L'],
                    ];
                    foreach ($dons as $row):
                    ?>
                    <tr>
                        <td><?= $row[0] ?></td>
                        <td><?= $row[1] ?></td>
                        <td><?= $row[2] ?></td>
                        <td>
                            <div class="actions">
                                <a href="/dons/<?= $row[0] ?>/modifier" class="btn btn-icon btn-outline btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-icon btn-outline btn-sm" title="Supprimer" onclick="openModal('modal-delete')"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
