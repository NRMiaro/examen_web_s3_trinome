<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-send"></i></div>
        <div class="page-header-text">
            <h1>Distributions</h1>
            <p>Suivi des distributions de dons aux sinistrés</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="/distributions/nouveau" class="btn btn-success">
            <i class="bi bi-plus-lg"></i> Nouvelle distribution
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
                <input type="text" placeholder="Rechercher une distribution…" data-table-search="table-distributions">
            </div>
            <button class="btn btn-sm btn-outline">
                <i class="bi bi-funnel"></i> Filtrer
            </button>
        </div>

        <div class="table-wrapper">
            <table class="data-table" id="table-distributions" data-paginate>
                <thead>
                    <tr>
                        <th data-sort>#</th>
                        <th data-sort>Date</th>
                        <th data-sort>Ville</th>
                        <th data-sort>Besoin</th>
                        <th data-sort>Qté distribuée</th>
                        <th data-sort>Bénéficiaires</th>
                        <th data-sort>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $distributions = [
                        [1,  '15/02/2026', 'Antananarivo', 'Eau potable',    '80 litres',    '45 familles', 'Complétée'],
                        [2,  '14/02/2026', 'Mahajanga',    'Nourriture',     '150 portions', '60 familles', 'Complétée'],
                        [3,  '13/02/2026', 'Toamasina',    'Couvertures',    '30 unités',    '30 familles', 'En cours'],
                        [4,  '12/02/2026', 'Antananarivo', 'Médicaments',    '20 boîtes',    '85 pers.',    'Planifiée'],
                        [5,  '11/02/2026', 'Fianarantsoa', 'Riz',            '300 kg',       '120 familles','Complétée'],
                        [6,  '10/02/2026', 'Mahajanga',    'Tentes',         '10 unités',    '10 familles', 'En cours'],
                        [7,  '09/02/2026', 'Antsirabe',    'Kits hygiène',   '40 kits',      '40 familles', 'Planifiée'],
                        [8,  '08/02/2026', 'Antananarivo', 'Vêtements',      '80 lots',      '55 familles', 'Complétée'],
                    ];
                    foreach ($distributions as $row):
                        $statusClass = match($row[6]) {
                            'Complétée' => 'badge-success',
                            'En cours'  => 'badge-info',
                            'Planifiée' => 'badge-warning',
                            default     => 'badge-neutral',
                        };
                    ?>
                    <tr>
                        <td><?= $row[0] ?></td>
                        <td><?= $row[1] ?></td>
                        <td><?= $row[2] ?></td>
                        <td><?= $row[3] ?></td>
                        <td><?= $row[4] ?></td>
                        <td><?= $row[5] ?></td>
                        <td><span class="badge <?= $statusClass ?>"><?= $row[6] ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="/distributions/1" class="btn btn-icon btn-outline btn-sm" title="Voir"><i class="bi bi-eye"></i></a>
                                <a href="/distributions/1/modifier" class="btn btn-icon btn-outline btn-sm" title="Modifier"><i class="bi bi-pencil"></i></a>
                                <button class="btn btn-icon btn-outline btn-sm" title="Supprimer" onclick="openModal('modal-delete')"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-pagination">
            <div class="pagination-info" data-pagination-info="table-distributions">8 sur 8 entrées</div>
            <div class="pagination-buttons" data-pagination="table-distributions"></div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
