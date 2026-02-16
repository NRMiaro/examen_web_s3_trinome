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
                <div class="form-group">
                    <label class="form-label">Date du don <span class="required">*</span></label>
                    <input type="datetime-local" name="date_don" class="form-input" value="<?= isset($don) ? date('Y-m-d\TH:i', strtotime($don['date_don'])) : '' ?>" required>
                </div>

                <h4 style="margin: 24px 0 16px; font-size: 1rem; font-weight: 600;">Détails du don</h4>

                <div id="don-details">
                    <?php if (isset($details) && !empty($details)): ?>
                        <?php foreach ($details as $detail): ?>
                        <div class="form-row" style="align-items: end;">
                            <div class="form-group">
                                <label class="form-label">Besoin</label>
                                <select name="besoins[]" class="form-select">
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach ($besoins as $besoin): ?>
                                    <option value="<?= $besoin['id'] ?>" <?= $detail['id_besoin'] == $besoin['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($besoin['nom']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Quantité</label>
                                <input type="number" name="quantites[]" class="form-input" placeholder="1000" value="<?= $detail['quantite'] ?>">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="form-row" style="align-items: end;">
                        <div class="form-group">
                            <label class="form-label">Besoin</label>
                            <select name="besoins[]" class="form-select">
                                <option value="">-- Sélectionner --</option>
                                <?php foreach ($besoins as $besoin): ?>
                                <option value="<?= $besoin['id'] ?>"><?= htmlspecialchars($besoin['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Quantité</label>
                            <input type="number" name="quantites[]" class="form-input" placeholder="1000">
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <button type="button" id="btn-ajouter-besoin" class="btn btn-outline btn-sm" style="margin-bottom: 20px;">
                    <i class="bi bi-plus-lg"></i> Ajouter un besoin
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
    document.getElementById('btn-ajouter-besoin').addEventListener('click', ajouterLigne);
});

function ajouterLigne() {
    const container = document.getElementById('don-details');
    const ligne = document.createElement('div');
    ligne.className = 'form-row';
    ligne.style.alignItems = 'end';
    ligne.innerHTML = `
        <div class="form-group">
            <select name="besoins[]" class="form-select">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($besoins as $besoin): ?>
                <option value="<?= $besoin['id'] ?>"><?= htmlspecialchars($besoin['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <input type="number" name="quantites[]" class="form-input" placeholder="1000">
        </div>
    `;
    container.appendChild(ligne);
}
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
