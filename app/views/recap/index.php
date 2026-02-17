<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">   
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-pie-chart-fill"></i></div>
        <div class="page-header-text">
            <h1>Récapitulation</h1>
            <p>Synthèse des besoins et des dons en montant</p>
        </div>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary" id="btn-refresh">
            <i class="bi bi-arrow-clockwise"></i> Actualiser
        </button>
    </div>
</div>

<!-- Cartes récapitulatives -->
<div class="stats-grid" id="recap-cards">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-clipboard-data"></i></div>
        <div class="stat-content">
            <h3>Besoins totaux</h3>
            <div class="stat-value" id="besoins-totaux"><?= number_format($recap['besoins_totaux'], 0, ',', ' ') ?> Ar</div>
            <div class="stat-trend">Montant total des demandes</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-check-circle"></i></div>
        <div class="stat-content">
            <h3>Besoins satisfaits</h3>
            <div class="stat-value" id="besoins-satisfaits"><?= number_format($recap['besoins_satisfaits'], 0, ',', ' ') ?> Ar</div>
            <div class="stat-trend up" id="pourcentage-satisfaction">
                <i class="bi bi-graph-up-arrow"></i> <?= $recap['pourcentage_satisfaction'] ?>% couvert
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-content">
            <h3>Besoins restants</h3>
            <div class="stat-value" id="besoins-restants"><?= number_format($recap['besoins_restants'], 0, ',', ' ') ?> Ar</div>
            <div class="stat-trend">Montant encore nécessaire</div>
        </div>
    </div>
</div>

<!-- Détail des dons satisfaits -->
<div class="card" style="margin-top: 24px; margin-bottom: 24px;">
    <div class="card-header">
        <h3 style="margin: 0;"><i class="bi bi-info-circle"></i> Détail des satisfactions</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div style="padding: 16px; background: #f8f9fa; border-radius: 8px;">
                <strong style="color: #666; font-size: 0.9em;">Dons en nature (valeur)</strong>
                <p style="font-size: 1.3em; margin: 8px 0 0 0; color: #4CAF50;" id="satisfaits-nature">
                    <?= number_format($recap['besoins_satisfaits_nature'], 0, ',', ' ') ?> Ar
                </p>
            </div>
            <div style="padding: 16px; background: #f8f9fa; border-radius: 8px;">
                <strong style="color: #666; font-size: 0.9em;">Dons financiers</strong>
                <p style="font-size: 1.3em; margin: 8px 0 0 0; color: #2196F3;" id="satisfaits-financier">
                    <?= number_format($recap['besoins_satisfaits_financier'], 0, ',', ' ') ?> Ar
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Tableau détaillé par besoin -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;"><i class="bi bi-table"></i> Détail par type de besoin</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Besoin</th>
                    <th class="text-end">Prix unitaire</th>
                    <th class="text-end">Qté demandée</th>
                    <th class="text-end">Qté donnée</th>
                    <th class="text-end">Montant demandé</th>
                    <th class="text-end">Montant reçu</th>
                    <th class="text-end">Montant restant</th>
                    <th class="text-center">Couverture</th>
                </tr>
            </thead>
            <tbody id="recap-table-body">
                <?php foreach ($recap_par_besoin as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['nom']) ?></strong></td>
                    <td class="text-end"><?= number_format($item['prix'], 0, ',', ' ') ?> Ar</td>
                    <td class="text-end"><?= number_format($item['quantite_demandee'], 0, ',', ' ') ?></td>
                    <td class="text-end"><?= number_format($item['quantite_donnee'], 0, ',', ' ') ?></td>
                    <td class="text-end"><?= number_format($item['montant_demande'], 0, ',', ' ') ?> Ar</td>
                    <td class="text-end text-success"><?= number_format($item['montant_donne'], 0, ',', ' ') ?> Ar</td>
                    <td class="text-end text-danger"><?= number_format($item['montant_restant'], 0, ',', ' ') ?> Ar</td>
                    <td class="text-center">
                        <div class="progress" style="height: 20px; min-width: 100px;">
                            <div class="progress-bar <?= $item['pourcentage'] >= 100 ? 'bg-success' : ($item['pourcentage'] >= 50 ? 'bg-warning' : 'bg-danger') ?>" 
                                 role="progressbar" 
                                 style="width: <?= min(100, $item['pourcentage']) ?>%;">
                                <?= $item['pourcentage'] ?>%
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script nonce="<?= Flight::app()->get('csp_nonce') ?>">
document.addEventListener('DOMContentLoaded', function() {
    const btnRefresh = document.getElementById('btn-refresh');
    
    btnRefresh.addEventListener('click', function() {
        // Animation du bouton
        btnRefresh.disabled = true;
        btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Chargement...';
        
        // Appel Ajax
        fetch('<?= BASE_URL ?>/api/recap')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const recap = data.data.recap;
                    const parBesoin = data.data.recap_par_besoin;
                    
                    // Mise à jour des cartes
                    document.getElementById('besoins-totaux').textContent = formatNumber(recap.besoins_totaux) + ' Ar';
                    document.getElementById('besoins-satisfaits').textContent = formatNumber(recap.besoins_satisfaits) + ' Ar';
                    document.getElementById('besoins-restants').textContent = formatNumber(recap.besoins_restants) + ' Ar';
                    document.getElementById('pourcentage-satisfaction').innerHTML = '<i class="bi bi-graph-up-arrow"></i> ' + recap.pourcentage_satisfaction + '% couvert';
                    
                    // Mise à jour détail satisfactions
                    document.getElementById('satisfaits-nature').textContent = formatNumber(recap.besoins_satisfaits_nature) + ' Ar';
                    document.getElementById('satisfaits-financier').textContent = formatNumber(recap.besoins_satisfaits_financier) + ' Ar';
                    
                    // Mise à jour du tableau
                    updateTable(parBesoin);
                    
                    // Feedback visuel
                    btnRefresh.classList.add('btn-success');
                    btnRefresh.innerHTML = '<i class="bi bi-check"></i> Actualisé';
                    
                    setTimeout(() => {
                        btnRefresh.classList.remove('btn-success');
                        btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Actualiser';
                        btnRefresh.disabled = false;
                    }, 1500);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                btnRefresh.classList.add('btn-danger');
                btnRefresh.innerHTML = '<i class="bi bi-x-circle"></i> Erreur';
                
                setTimeout(() => {
                    btnRefresh.classList.remove('btn-danger');
                    btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Actualiser';
                    btnRefresh.disabled = false;
                }, 2000);
            });
    });
    
    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }
    
    function updateTable(data) {
        const tbody = document.getElementById('recap-table-body');
        tbody.innerHTML = '';
        
        data.forEach(item => {
            const progressClass = item.pourcentage >= 100 ? 'bg-success' : (item.pourcentage >= 50 ? 'bg-warning' : 'bg-danger');
            
            const row = `
                <tr>
                    <td><strong>${escapeHtml(item.nom)}</strong></td>
                    <td class="text-end">${formatNumber(item.prix)} Ar</td>
                    <td class="text-end">${formatNumber(item.quantite_demandee)}</td>
                    <td class="text-end">${formatNumber(item.quantite_donnee)}</td>
                    <td class="text-end">${formatNumber(item.montant_demande)} Ar</td>
                    <td class="text-end text-success">${formatNumber(item.montant_donne)} Ar</td>
                    <td class="text-end text-danger">${formatNumber(item.montant_restant)} Ar</td>
                    <td class="text-center">
                        <div class="progress" style="height: 20px; min-width: 100px;">
                            <div class="progress-bar ${progressClass}" 
                                 role="progressbar" 
                                 style="width: ${Math.min(100, item.pourcentage)}%;">
                                ${item.pourcentage}%
                            </div>
                        </div>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
</script>

<style nonce="<?= Flight::app()->get('csp_nonce') ?>">
.spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
.progress {
    background-color: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
}
.progress-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.85em;
    font-weight: 500;
}
</style>

<?php include __DIR__ . '/../include/footer.php'; ?>
