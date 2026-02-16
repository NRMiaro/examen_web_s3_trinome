<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-clipboard-check"></i></div>
        <div class="page-header-text">
            <h1>Besoins</h1>
            <p>Inventaire des besoins identifiés par zone sinistrée</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="/besoins/nouveau" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Ajouter un besoin
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-toolbar">
            <div class="table-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Rechercher un besoin…" data-table-search="table-besoins">
            </div>
            <button class="btn btn-sm btn-outline">
                <i class="bi bi-funnel"></i> Filtrer
            </button>
        </div>

        <div class="table-wrapper">
            <table class="data-table" id="table-besoins" data-paginate>
                <thead>
                    <tr>
                        <th data-sort>#</th>
                        <th data-sort>Type</th>
                        <th data-sort>Nom</th>
                        <th data-sort>Prix unit.</th>
                        <th data-sort>Ville</th>
                        <th data-sort>Qté requise</th>
                        <th data-sort>Qté collectée</th>
                        <th data-sort>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $besoins = [
                        [1, 'Nature',   'Eau potable',    '1 000 Ar',    'Antananarivo', 100,  100, 'Couvert'],
                        [2, 'Matériel', 'Couvertures',    '5 000 Ar',    'Antananarivo', 50,   30,  'Partiel'],
                        [3, 'Nature',   'Nourriture',     '15 000 Ar',   'Mahajanga',    200,  200, 'Couvert'],
                        [4, 'Matériel', 'Tentes',         '80 000 Ar',   'Toamasina',    15,   5,   'Critique'],
                        [5, 'Argent',   'Fonds urgence',  '—',           'Fianarantsoa', '—',  '—', 'En attente'],
                        [6, 'Nature',   'Riz',            '2 500 Ar',    'Antananarivo', 500,  450, 'Partiel'],
                        [7, 'Matériel', 'Kits hygiène',   '12 000 Ar',   'Antsirabe',    60,   0,   'Critique'],
                        [8, 'Matériel', 'Médicaments',    '25 000 Ar',   'Mahajanga',    30,   20,  'Partiel'],
                    ];
                    foreach ($besoins as $row):
                        $statusClass = match($row[7]) {
                            'Couvert'    => 'badge-success',
                            'Partiel'    => 'badge-warning',
                            'Critique'   => 'badge-danger',
                            'En attente' => 'badge-info',
                            default      => 'badge-neutral',
                        };
                    ?>
                    <tr>
                        <td><?= $row[0] ?></td>
                        <td><span class="badge badge-neutral"><?= $row[1] ?></span></td>
                        <td><?= $row[2] ?></td>
                        <td><?= $row[3] ?></td>
                        <td><?= $row[4] ?></td>
                        <td><?= $row[5] ?></td>
                        <td><?= $row[6] ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $row[7] ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="/besoins/1" class="btn btn-icon btn-outline btn-sm" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="/besoins/1/modifier" class="btn btn-icon btn-outline btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-icon btn-outline btn-sm" title="Supprimer" onclick="openModal('modal-delete')"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            <div class="pagination-info" data-pagination-info="table-besoins">8 sur 8 entrées</div>
            <div class="pagination-buttons" data-pagination="table-besoins"></div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
