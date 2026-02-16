<?php include __DIR__ . '/../include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-clipboard-check"></i></div>
        <div class="page-header-text">
            <h1><?= $page_title ?? 'Besoin' ?></h1>
            <p>Remplissez le formulaire ci-dessous</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-container">
            <form action="<?= $action ?? '/besoins' ?>" method="POST">
                <div class="form-group">
                    <label class="form-label">Nom <span class="required">*</span></label>
                    <input type="text" name="nom" class="form-input" placeholder="Ex: Riz" value="<?= htmlspecialchars($besoin['nom'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Type <span class="required">*</span></label>
                    <select name="id_type_besoin" class="form-select" required>
                        <option value="">-- Sélectionner un type --</option>
                        <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= (isset($besoin) && $besoin['id_type_besoin'] == $type['id']) ? 'selected' : '' ?>>
                            <?= ucfirst(htmlspecialchars($type['nom'])) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Prix unitaire (Ar) <span class="required">*</span></label>
                    <input type="number" name="prix" class="form-input" placeholder="1000" value="<?= $besoin['prix'] ?? '' ?>" required>
                </div>

                <div class="form-actions">
                    <a href="/besoins" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../include/footer.php'; ?>
