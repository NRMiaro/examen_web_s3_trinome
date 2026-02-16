<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-clipboard-check"></i></div>
        <div class="page-header-text">
            <h1><?= $page_title ?? 'Ajouter un besoin' ?></h1>
            <p>Créez un nouveau type de besoin</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-container">
            <form action="<?= BASE_URL ?>/besoins/creer" method="POST">
                <div class="form-group">
                    <label class="form-label">Nom du besoin <span class="required">*</span></label>
                    <input type="text" name="nom" class="form-input" placeholder="Ex: Riz" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Type <span class="required">*</span></label>
                    <select name="id_type_besoin" class="form-select" required>
                        <option value="">-- Sélectionner un type --</option>
                        <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>">
                            <?= ucfirst(htmlspecialchars($type['nom'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Prix unitaire (Ar) <span class="required">*</span></label>
                    <input type="number" name="prix" class="form-input" placeholder="1000" min="0" required>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/besoins" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
