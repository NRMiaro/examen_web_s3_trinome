<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-box-seam"></i></div>
        <div class="page-header-text">
            <h1>Collectes</h1>
            <p>Gestion des collectes de dons reçus</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="/collectes/nouveau" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nouvelle collecte
        </a>
        <button class="btn btn-outline">
            <i class="bi bi-download"></i> Exporter
        </button>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="table-toolbar">
            <div class="table-search">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Rechercher une collecte…" data-table-search="table-collectes">
            </div>
            <button class="btn btn-sm btn-outline">
                <i class="bi bi-funnel"></i> Filtrer
            </button>
        </div>

        <div class="table-wrapper">
            <table class="data-table" id="table-collectes" data-paginate>
                <thead>
                    <tr>
                        <th data-sort>#</th>
                        <th data-sort>Date</th>
                        <th data-sort>Ville</th>
                        <th data-sort>Type</th>
                        <th data-sort>Besoin</th>
                        <th data-sort>Quantité</th>
                        <th data-sort>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $collectes = [
                        [1,  '15/02/2026', 'Antananarivo', 'Nature',    'Eau potable',    '100 litres',     'Délivré'],
                        [2,  '14/02/2026', 'Mahajanga',    'Matériel',  'Couvertures',    '50 unités',      'En attente'],
                        [3,  '13/02/2026', 'Mahajanga',    'Nature',    'Nourriture',     '200 portions',   'Délivré'],
                        [4,  '12/02/2026', 'Antananarivo', 'Matériel',  'Médicaments',    '30 boîtes',      'En attente'],
                        [5,  '11/02/2026', 'Toamasina',    'Matériel',  'Tentes',         '15 unités',      'En transit'],
                        [6,  '10/02/2026', 'Fianarantsoa', 'Argent',    'Fonds urgence',  '2 500 000 Ar',   'Délivré'],
                        [7,  '09/02/2026', 'Antananarivo', 'Nature',    'Riz',            '500 kg',         'Délivré'],
                        [8,  '08/02/2026', 'Mahajanga',    'Matériel',  'Vêtements',      '120 lots',       'En attente'],
                        [9,  '07/02/2026', 'Toamasina',    'Nature',    'Eau potable',    '80 litres',      'Délivré'],
                        [10, '06/02/2026', 'Antsirabe',    'Argent',    'Fonds recons.',   '1 000 000 Ar',  'En attente'],
                        [11, '05/02/2026', 'Antananarivo', 'Matériel',  'Kits hygiène',   '60 kits',        'Délivré'],
                        [12, '04/02/2026', 'Mahajanga',    'Nature',    'Nourriture',     '150 portions',   'En transit'],
                    ];
                    foreach ($collectes as $row):
                        $statusClass = match($row[6]) {
                            'Délivré'    => 'badge-success',
                            'En attente' => 'badge-warning',
                            'En transit' => 'badge-info',
                            default      => 'badge-neutral',
                        };
                    ?>
                    <tr>
                        <td><?= $row[0] ?></td>
                        <td><?= $row[1] ?></td>
                        <td><?= $row[2] ?></td>
                        <td><span class="badge badge-neutral"><?= $row[3] ?></span></td>
                        <td><?= $row[4] ?></td>
                        <td><?= $row[5] ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $row[6] ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="/collectes/1" class="btn btn-icon btn-outline btn-sm" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="/collectes/1/modifier" class="btn btn-icon btn-outline btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-icon btn-outline btn-sm" title="Supprimer" onclick="openModal('modal-delete')"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            <div class="pagination-info" data-pagination-info="table-collectes">12 sur 12 entrées</div>
            <div class="pagination-buttons" data-pagination="table-collectes"></div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
