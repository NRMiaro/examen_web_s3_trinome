<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">   
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-pie-chart-fill"></i></div>
        <div class="page-header-text">
            <h1>Récapitulation</h1>
            <p>Synthèse des besoins, dons validés et achats</p>
        </div>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary" id="btn-refresh" style="padding: 10px 20px; font-size: 1em; border-radius: 6px; background-color: #007bff; color: white; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;">
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
            <h3>Besoins couverts</h3>
            <div class="stat-value" id="besoins-satisfaits"><?= number_format($recap['besoins_satisfaits'], 0, ',', ' ') ?> Ar</div>
            <div class="stat-trend up" id="pourcentage-satisfaction">
                <i class="bi bi-graph-up-arrow"></i> <?= $recap['pourcentage_satisfaction'] ?>% couvert
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background: #fff3e0; color: #e65100;"><i class="bi bi-exclamation-triangle"></i></div>
        <div class="stat-content">
            <h3>Besoins restants</h3>
            <div class="stat-value" id="besoins-restants"><?= number_format($recap['besoins_restants'], 0, ',', ' ') ?> Ar</div>
            <div class="stat-trend">Montant encore nécessaire</div>
        </div>
    </div>
</div>

<!-- Détail des sources de couverture -->
<div class="card" style="margin-top: 24px; margin-bottom: 24px;">
    <div class="card-header">
        <h3 style="margin: 0;"><i class="bi bi-info-circle"></i> Sources de couverture</h3>
    </div>
    <div class="card-body">
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px;">
            <div style="padding: 16px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #4CAF50;">
                <strong style="color: #666; font-size: 0.85em;">Dons validés (valeur)</strong>
                <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #4CAF50; font-weight: 600;" id="montant-valide">
                    <?= number_format($recap['montant_valide'], 0, ',', ' ') ?> Ar
                </p>
            </div>
            <div style="padding: 16px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #FF9800;">
                <strong style="color: #666; font-size: 0.85em;">Achats effectués (valeur)</strong>
                <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #FF9800; font-weight: 600;" id="montant-achete">
                    <?= number_format($recap['montant_achete'], 0, ',', ' ') ?> Ar
                </p>
            </div>
            <div style="padding: 16px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #2196F3;">
                <strong style="color: #666; font-size: 0.85em;">Dons financiers reçus</strong>
                <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #2196F3; font-weight: 600;" id="dons-financiers">
                    <?= number_format($recap['dons_financiers_total'], 0, ',', ' ') ?> Ar
                </p>
            </div>
            <div style="padding: 16px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #9C27B0;">
                <strong style="color: #666; font-size: 0.85em;">Solde caisse</strong>
                <p style="font-size: 1.2em; margin: 8px 0 0 0; color: #9C27B0; font-weight: 600;" id="solde-caisse">
                    <?= number_format($recap['solde_caisse'], 0, ',', ' ') ?> Ar
                </p>
                <small style="color: #999;" id="depense-achats">Dépensé : <?= number_format($recap['total_depense_achats'], 0, ',', ' ') ?> Ar (avec frais)</small>
            </div>
        </div>
    </div>
</div>

<!-- Barre de progression globale -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-body">
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
            <strong>Couverture globale</strong>
            <span id="pct-global"><?= $recap['pourcentage_satisfaction'] ?>%</span>
        </div>
        <div style="background: #e9ecef; border-radius: 6px; height: 24px; overflow: hidden;">
            <?php
                $pctV = $recap['besoins_totaux'] > 0 ? round(($recap['montant_valide'] / $recap['besoins_totaux']) * 100, 1) : 0;
                $pctA = $recap['besoins_totaux'] > 0 ? round(($recap['montant_achete'] / $recap['besoins_totaux']) * 100, 1) : 0;
            ?>
            <div style="display: flex; height: 100%;" id="progress-global">
                <div style="width: <?= min(100, $pctV) ?>%; background: #4CAF50; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8em; font-weight: 500;" title="Dons validés"><?= $pctV > 5 ? $pctV . '%' : '' ?></div>
                <div style="width: <?= min(100 - $pctV, $pctA) ?>%; background: #FF9800; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.8em; font-weight: 500;" title="Achats"><?= $pctA > 5 ? $pctA . '%' : '' ?></div>
            </div>
        </div>
        <div style="display: flex; gap: 20px; margin-top: 8px; font-size: 0.85em; color: #666;">
            <span><span style="display: inline-block; width: 12px; height: 12px; background: #4CAF50; border-radius: 2px; margin-right: 4px;"></span> Dons validés</span>
            <span><span style="display: inline-block; width: 12px; height: 12px; background: #FF9800; border-radius: 2px; margin-right: 4px;"></span> Achats</span>
            <span><span style="display: inline-block; width: 12px; height: 12px; background: #e9ecef; border-radius: 2px; margin-right: 4px;"></span> Restant</span>
        </div>
    </div>
</div>

<!-- Tableau détaillé par besoin -->
<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;"><i class="bi bi-table"></i> Détail par type de besoin</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Besoin</th>
                    <th style="text-align: right;">Prix unit.</th>
                    <th style="text-align: right;">Demandé</th>
                    <th style="text-align: right;">Validé (dons)</th>
                    <th style="text-align: right;">Acheté</th>
                    <th style="text-align: right;">Restant</th>
                    <th style="text-align: right;">Montant demandé</th>
                    <th style="text-align: right;">Montant couvert</th>
                    <th style="text-align: center;">Couverture</th>
                </tr>
            </thead>
            <tbody id="recap-table-body">
                <?php foreach ($recap_par_besoin as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['nom']) ?></strong></td>
                    <td style="text-align: right;"><?= number_format($item['prix'], 0, ',', ' ') ?> Ar</td>
                    <td style="text-align: right;"><?= number_format($item['quantite_demandee'], 0, ',', ' ') ?></td>
                    <td style="text-align: right; color: #4CAF50;"><?= number_format($item['quantite_validee'], 0, ',', ' ') ?></td>
                    <td style="text-align: right; color: #FF9800;"><?= number_format($item['quantite_achetee'], 0, ',', ' ') ?></td>
                    <td style="text-align: right; color: #F44336; font-weight: 600;"><?= number_format($item['quantite_restante'], 0, ',', ' ') ?></td>
                    <td style="text-align: right;"><?= number_format($item['montant_demande'], 0, ',', ' ') ?> Ar</td>
                    <td style="text-align: right; color: #4CAF50; font-weight: 600;"><?= number_format($item['montant_couvert'], 0, ',', ' ') ?> Ar</td>
                    <td style="text-align: center;">
                        <div style="background: #e9ecef; border-radius: 4px; height: 20px; min-width: 100px; overflow: hidden; position: relative;">
                            <?php
                                $pV = $item['montant_demande'] > 0 ? round(($item['montant_valide'] / $item['montant_demande']) * 100) : 0;
                                $pA = $item['montant_demande'] > 0 ? round(($item['montant_achete'] / $item['montant_demande']) * 100) : 0;
                            ?>
                            <div style="display: flex; height: 100%;">
                                <div style="width: <?= min(100, $pV) ?>%; background: #4CAF50;"></div>
                                <div style="width: <?= min(100 - $pV, $pA) ?>%; background: #FF9800;"></div>
                            </div>
                            <span style="position: absolute; top: 0; left: 0; right: 0; text-align: center; line-height: 20px; font-size: 0.8em; font-weight: 600; color: <?= $item['pourcentage'] > 40 ? 'white' : '#333' ?>;">
                                <?= $item['pourcentage'] ?>%
                            </span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<script nonce="<?= Flight::app()->get('csp_nonce') ?>">
document.addEventListener('DOMContentLoaded', function() {
    var btnRefresh = document.getElementById('btn-refresh');
    
    btnRefresh.addEventListener('click', function() {
        btnRefresh.disabled = true;
        btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Chargement...';
        
        fetch('<?= BASE_URL ?>/api/recap')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var r = data.data.recap;
                    var items = data.data.recap_par_besoin;
                    
                    // Cartes
                    document.getElementById('besoins-totaux').textContent = fmt(r.besoins_totaux) + ' Ar';
                    document.getElementById('besoins-satisfaits').textContent = fmt(r.besoins_satisfaits) + ' Ar';
                    document.getElementById('besoins-restants').textContent = fmt(r.besoins_restants) + ' Ar';
                    document.getElementById('pourcentage-satisfaction').innerHTML = '<i class="bi bi-graph-up-arrow"></i> ' + r.pourcentage_satisfaction + '% couvert';
                    
                    // Sources
                    document.getElementById('montant-valide').textContent = fmt(r.montant_valide) + ' Ar';
                    document.getElementById('montant-achete').textContent = fmt(r.montant_achete) + ' Ar';
                    document.getElementById('dons-financiers').textContent = fmt(r.dons_financiers_total) + ' Ar';
                    document.getElementById('solde-caisse').textContent = fmt(r.solde_caisse) + ' Ar';
                    document.getElementById('depense-achats').textContent = 'Dépensé : ' + fmt(r.total_depense_achats) + ' Ar (avec frais)';
                    
                    // Barre globale
                    document.getElementById('pct-global').textContent = r.pourcentage_satisfaction + '%';
                    var pctV = r.besoins_totaux > 0 ? Math.round((r.montant_valide / r.besoins_totaux) * 100 * 10) / 10 : 0;
                    var pctA = r.besoins_totaux > 0 ? Math.round((r.montant_achete / r.besoins_totaux) * 100 * 10) / 10 : 0;
                    var pg = document.getElementById('progress-global');
                    pg.innerHTML = '<div style="width:' + Math.min(100, pctV) + '%;background:#4CAF50;display:flex;align-items:center;justify-content:center;color:white;font-size:0.8em;font-weight:500;" title="Dons validés">' + (pctV > 5 ? pctV + '%' : '') + '</div>'
                        + '<div style="width:' + Math.min(100 - pctV, pctA) + '%;background:#FF9800;display:flex;align-items:center;justify-content:center;color:white;font-size:0.8em;font-weight:500;" title="Achats">' + (pctA > 5 ? pctA + '%' : '') + '</div>';
                    
                    // Tableau
                    var tbody = document.getElementById('recap-table-body');
                    tbody.innerHTML = '';
                    items.forEach(function(it) {
                        var pvB = it.montant_demande > 0 ? Math.round((it.montant_valide / it.montant_demande) * 100) : 0;
                        var paB = it.montant_demande > 0 ? Math.round((it.montant_achete / it.montant_demande) * 100) : 0;
                        var txtColor = it.pourcentage > 40 ? 'white' : '#333';
                        tbody.insertAdjacentHTML('beforeend', 
                            '<tr>' +
                            '<td><strong>' + esc(it.nom) + '</strong></td>' +
                            '<td style="text-align:right;">' + fmt(it.prix) + ' Ar</td>' +
                            '<td style="text-align:right;">' + fmt(it.quantite_demandee) + '</td>' +
                            '<td style="text-align:right;color:#4CAF50;">' + fmt(it.quantite_validee) + '</td>' +
                            '<td style="text-align:right;color:#FF9800;">' + fmt(it.quantite_achetee) + '</td>' +
                            '<td style="text-align:right;color:#F44336;font-weight:600;">' + fmt(it.quantite_restante) + '</td>' +
                            '<td style="text-align:right;">' + fmt(it.montant_demande) + ' Ar</td>' +
                            '<td style="text-align:right;color:#4CAF50;font-weight:600;">' + fmt(it.montant_couvert) + ' Ar</td>' +
                            '<td style="text-align:center;"><div style="background:#e9ecef;border-radius:4px;height:20px;min-width:100px;overflow:hidden;position:relative;">' +
                            '<div style="display:flex;height:100%;"><div style="width:' + Math.min(100, pvB) + '%;background:#4CAF50;"></div><div style="width:' + Math.min(100-pvB, paB) + '%;background:#FF9800;"></div></div>' +
                            '<span style="position:absolute;top:0;left:0;right:0;text-align:center;line-height:20px;font-size:0.8em;font-weight:600;color:' + txtColor + ';">' + it.pourcentage + '%</span></div></td>' +
                            '</tr>'
                        );
                    });
                    
                    // Feedback
                    btnRefresh.style.backgroundColor = '#28a745';
                    btnRefresh.innerHTML = '<i class="bi bi-check"></i> Actualisé';
                    setTimeout(function() {
                        btnRefresh.style.backgroundColor = '#007bff';
                        btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Actualiser';
                        btnRefresh.disabled = false;
                    }, 1500);
                }
            })
            .catch(function(error) {
                console.error('Erreur:', error);
                btnRefresh.style.backgroundColor = '#dc3545';
                btnRefresh.innerHTML = '<i class="bi bi-x-circle"></i> Erreur';
                setTimeout(function() {
                    btnRefresh.style.backgroundColor = '#007bff';
                    btnRefresh.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Actualiser';
                    btnRefresh.disabled = false;
                }, 2000);
            });
    });
    
    function fmt(n) { return new Intl.NumberFormat('fr-FR').format(n); }
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
});
</script>

<style nonce="<?= Flight::app()->get('csp_nonce') ?>">
.spin { animation: spin 1s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>

<?php include __DIR__ . '/../include/footer.php'; ?>
