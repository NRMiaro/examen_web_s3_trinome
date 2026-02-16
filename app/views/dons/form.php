<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-gift-fill"></i></div>
        <div class="page-header-text">
            <h1><?= $page_title ?? 'Don' ?></h1>
            <p>Enregistrer un don reçu</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-container">
            <form action="<?= $action ?? (BASE_URL . '/dons') ?>" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date du don <span class="required">*</span></label>
                        <input type="datetime-local" name="date_don" class="form-input" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Type de don <span class="required">*</span></label>
                        <select name="type_don" id="type-don-select" class="form-select" required>
                            <option value="">-- Sélectionner le type --</option>
                            <option value="financier">Financier (argent)</option>
                            <option value="besoin">Don de biens (nature / matériel)</option>
                        </select>
                    </div>
                </div>

                <!-- Section Don Financier -->
                <div id="section-financier" style="display: none;">
                    <h4 style="margin: 24px 0 16px; font-size: 1rem; font-weight: 600;">
                        <i class="bi bi-cash-coin"></i> Montant du don
                    </h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Montant (Ar) <span class="required">*</span></label>
                            <input type="number" name="montant" id="input-montant" class="form-input" min="1" placeholder="100000">
                        </div>
                    </div>
                </div>

                <!-- Section Don de Biens -->
                <div id="section-biens" style="display: none;">
                    <h4 style="margin: 24px 0 16px; font-size: 1rem; font-weight: 600;">
                        <i class="bi bi-box-seam-fill"></i> Détails des biens donnés
                    </h4>

                    <div id="don-details">
                        <div class="don-ligne form-row" style="align-items: end;">
                            <div class="form-group" style="flex: 2;">
                                <label class="form-label">Besoin</label>
                                <select name="besoins[]" class="form-select">
                                    <option value="">-- Sélectionner un besoin --</option>
                                    <?php foreach ($all_besoins ?? [] as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nom']) ?> (<?= $b['type_nom'] ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="quantites[]" class="form-input" min="1" placeholder="1000">
                            </div>
                        </div>
                    </div>

                    <button type="button" id="btn-ajouter-ligne" class="btn btn-outline btn-sm" style="margin-bottom: 20px;">
                        <i class="bi bi-plus-lg"></i> Ajouter une ligne
                    </button>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/dons" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-success">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= Flight::app()->get('csp_nonce') ?>">
document.addEventListener('DOMContentLoaded', function() {
    var typeDonSelect = document.getElementById('type-don-select');
    var sectionFin = document.getElementById('section-financier');
    var sectionBiens = document.getElementById('section-biens');
    var inputMontant = document.getElementById('input-montant');

    // Toggle entre les deux sections
    typeDonSelect.addEventListener('change', function() {
        if (this.value === 'financier') {
            sectionFin.style.display = '';
            sectionBiens.style.display = 'none';
            inputMontant.required = true;
            // Désactiver les champs biens
            document.querySelectorAll('#don-details select, #don-details input').forEach(function(el) {
                el.removeAttribute('required');
            });
        } else if (this.value === 'besoin') {
            sectionFin.style.display = 'none';
            sectionBiens.style.display = '';
            inputMontant.required = false;
            inputMontant.value = '';
        } else {
            sectionFin.style.display = 'none';
            sectionBiens.style.display = 'none';
            inputMontant.required = false;
        }
    });

    // Ajouter une ligne de besoin
    document.getElementById('btn-ajouter-ligne').addEventListener('click', function() {
        var container = document.getElementById('don-details');
        var ligne = document.createElement('div');
        ligne.className = 'don-ligne form-row';
        ligne.style.alignItems = 'end';

        var besoinsOptions = '<option value="">-- Sélectionner un besoin --</option>';
        <?php foreach ($all_besoins ?? [] as $b): ?>
        besoinsOptions += '<option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nom']) ?> (<?= $b['type_nom'] ?>)</option>';
        <?php endforeach; ?>

        ligne.innerHTML =
            '<div class="form-group" style="flex: 2;">' +
                '<label class="form-label">Besoin</label>' +
                '<select name="besoins[]" class="form-select">' + besoinsOptions + '</select>' +
            '</div>' +
            '<div class="form-group">' +
                '<label class="form-label">Quantité</label>' +
                '<input type="number" name="quantites[]" class="form-input" min="1" placeholder="1000">' +
            '</div>' +
            '<div class="form-group" style="flex: 0;">' +
                '<button type="button" class="btn btn-icon btn-outline btn-sm btn-suppr-ligne" title="Supprimer">' +
                    '<i class="bi bi-trash"></i>' +
                '</button>' +
            '</div>';

        container.appendChild(ligne);

        // Attacher suppression
        ligne.querySelector('.btn-suppr-ligne').addEventListener('click', function() {
            ligne.remove();
        });
    });
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
