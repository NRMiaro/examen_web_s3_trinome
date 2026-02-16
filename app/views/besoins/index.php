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
        <a href="/besoins/nouveau" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouveau besoin
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
                    <?php
                    $besoins = [
                        [1, 'Riz', 'nature', 1000],
                        [2, 'Huile', 'nature', 4000],
                    ];
                    foreach ($besoins as $row):
                    ?>
                    <tr>
                        <td><?= $row[0] ?></td>
                        <td><strong><?= $row[1] ?></strong></td>
                        <td><span class="badge badge-neutral"><?= ucfirst($row[2]) ?></span></td>
                        <td><?= number_format($row[3], 0, ',', ' ') ?> Ar</td>
                        <td>
                            <div class="actions">
                                <a href="/besoins/<?= $row[0] ?>/modifier" class="btn btn-icon btn-outline btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
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
