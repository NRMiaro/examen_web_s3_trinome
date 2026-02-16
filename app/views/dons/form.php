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
            <form action="<?= $action ?? '/dons' ?>" method="POST">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date du don <span class="required">*</span></label>
                        <input type="datetime-local" name="date_don" class="form-input" value="<?= isset($don) ? date('Y-m-d\TH:i', strtotime($don['date_don'])) : '' ?>" required>
                    </div>
                </div>

                <h4 style="margin: 24px 0 16px; font-size: 1rem; font-weight: 600;">Détails du don</h4>

                <div id="don-details">
                    <div class="form-row" style="align-items: end;">
                        <div class="form-group">
                            <label class="form-label">Type de don</label>
                            <select name="types[]" class="form-select type-select">
                                <option value="">-- Sélectionner --</option>
                                <option value="financier">Financier</option>
                                <option value="besoin">Besoin</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Besoin (si applicable)</label>
                            <select name="besoins[]" class="form-select besoin-select" style="display:none;">
                                <option value="">-- Sélectionner un besoin --</option>
                                <?php foreach ($all_besoins ?? [] as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantité</label>
                            <input type="number" name="quantites[]" class="form-input" placeholder="1000">
                        </div>
                    </div>
                </div>

                <button type="button" id="btn-ajouter-besoin" class="btn btn-outline btn-sm" style="margin-bottom: 20px;">
                    <i class="bi bi-plus-lg"></i> Ajouter une ligne
                </button>

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
    document.getElementById('btn-ajouter-besoin')?.addEventListener('click', ajouterLigne);

    // attacher listeners aux selects existants
    document.querySelectorAll('.type-select').forEach(function(typeSelect) {
        const row = typeSelect.closest('.form-row');
        const besoinSelect = row ? row.querySelector('.besoin-select') : null;
        typeSelect.addEventListener('change', function() {
            if (besoinSelect) {
                if (this.value === 'besoin') {
                    besoinSelect.style.display = '';
                } else {
                    besoinSelect.style.display = 'none';
                    besoinSelect.value = '';
                }
            }
        });
    });
});

function ajouterLigne() {
    const container = document.getElementById('don-details');
    const ligne = document.createElement('div');
    ligne.className = 'form-row';
    ligne.style.alignItems = 'end';
    // construire ligne avec type limité à financier/besoin et select de besoins caché
    const besoinsOptions = `
        <option value="">-- Sélectionner un besoin --</option>
        <?php foreach ($all_besoins ?? [] as $b): ?>
        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nom']) ?></option>
        <?php endforeach; ?>
    `;

    ligne.innerHTML = `
        <div class="form-group">
            <label class="form-label">Type de don</label>
            <select name="types[]" class="form-select type-select">
                <option value="">-- Sélectionner --</option>
                <option value="financier">Financier</option>
                <option value="besoin">Besoin</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Besoin (si applicable)</label>
            <select name="besoins[]" class="form-select besoin-select" style="display:none;">
                ${besoinsOptions}
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Quantité</label>
            <input type="number" name="quantites[]" class="form-input" placeholder="1000">
        </div>
    `;

    container.appendChild(ligne);

    // attacher le listener sur le select type juste créé
    const typeSelect = ligne.querySelector('.type-select');
    const besoinSelect = ligne.querySelector('.besoin-select');
    typeSelect.addEventListener('change', function() {
        if (this.value === 'besoin') {
            besoinSelect.style.display = '';
        } else {
            besoinSelect.style.display = 'none';
            besoinSelect.value = '';
        }
    });
}
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
