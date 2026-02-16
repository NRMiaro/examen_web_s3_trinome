<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-grid-1x2"></i></div>
        <div class="page-header-text">
            <h1>Tableau de bord</h1>
            <p>Vue d'ensemble des collectes et distributions</p>
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

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-box-seam"></i></div>
        <div class="stat-content">
            <h3>Total collectes</h3>
            <div class="stat-value">1 248</div>
            <div class="stat-trend up"><i class="bi bi-arrow-up"></i> +12.5% ce mois</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-send"></i></div>
        <div class="stat-content">
            <h3>Distributions</h3>
            <div class="stat-value">876</div>
            <div class="stat-trend up"><i class="bi bi-arrow-up"></i> +8.2% ce mois</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-clipboard-check"></i></div>
        <div class="stat-content">
            <h3>Besoins en attente</h3>
            <div class="stat-value">342</div>
            <div class="stat-trend down"><i class="bi bi-arrow-down"></i> -3.1% ce mois</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-geo-alt"></i></div>
        <div class="stat-content">
            <h3>Villes couvertes</h3>
            <div class="stat-value">24</div>
            <div class="stat-trend up"><i class="bi bi-arrow-up"></i> +2 nouvelles</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Dernières collectes</h3>
        <a href="/collectes" class="btn btn-sm btn-outline">Voir tout</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Ville</th>
                        <th>Besoin</th>
                        <th>Quantité</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>15/02/2026</td>
                        <td>Antananarivo</td>
                        <td>Eau potable</td>
                        <td>100 litres</td>
                        <td><span class="badge badge-success">Délivré</span></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td>14/02/2026</td>
                        <td>Mahajanga</td>
                        <td>Couvertures</td>
                        <td>50 unités</td>
                        <td><span class="badge badge-warning">En attente</span></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td>13/02/2026</td>
                        <td>Mahajanga</td>
                        <td>Nourriture</td>
                        <td>200 portions</td>
                        <td><span class="badge badge-success">Délivré</span></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td>12/02/2026</td>
                        <td>Antananarivo</td>
                        <td>Médicaments</td>
                        <td>30 boîtes</td>
                        <td><span class="badge badge-warning">En attente</span></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td>11/02/2026</td>
                        <td>Toamasina</td>
                        <td>Tentes</td>
                        <td>15 unités</td>
                        <td><span class="badge badge-info">En transit</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
