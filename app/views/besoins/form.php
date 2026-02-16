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
            <form action="<?= $action ?? '/besoins' ?>" method="POST" id="besoin-form">
                <div class="form-group">
                    <label class="form-label">Ville <span class="required">*</span></label>
                    <select name="id_ville" class="form-select" required>
                        <option value="">-- Sélectionner une ville --</option>
                        <?php foreach ($villes as $ville): ?>
                        <option value="<?= $ville['id'] ?>" <?= (isset($besoin) && isset($ville_selectionnee) && $ville['id'] == $ville_selectionnee) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ville['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Date du besoin <span class="required">*</span></label>
                    <input type="datetime-local" name="date_besoin" class="form-input" value="<?= $date_besoin ?? date('Y-m-d\TH:i') ?>" required>
                </div>

                <hr style="margin: 30px 0;">
                <h3 style="margin-bottom: 20px;">Ajouter des besoins</h3>

                <div id="besoins-container">
                    <div class="besoin-item" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                        <div class="form-row">
                            <div class="form-group" style="flex: 1; margin-right: 10px;">
                                <label class="form-label">Nom du besoin <span class="required">*</span></label>
                                <input type="text" name="nom_besoin[]" class="form-input" placeholder="Ex: Riz" required>
                            </div>

                            <div class="form-group" style="flex: 1; margin-right: 10px;">
                                <label class="form-label">Type <span class="required">*</span></label>
                                <select name="id_type_besoin[]" class="form-select" required>
                                    <option value="">-- Sélectionner un type --</option>
                                    <?php foreach ($types as $type): ?>
                                    <option value="<?= $type['id'] ?>">
                                        <?= ucfirst(htmlspecialchars($type['nom'])) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group" style="flex: 1;">
                                <label class="form-label">Prix unitaire (Ar) <span class="required">*</span></label>
                                <input type="number" name="prix_besoin[]" class="form-input" placeholder="1000" min="0" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantité <span class="required">*</span></label>
                            <input type="number" name="quantite[]" class="form-input" placeholder="1" min="1" required>
                        </div>

                        <button type="button" class="btn btn-sm btn-danger remove-besoin" style="display: none;">Supprimer</button>
                    </div>
                </div>

                <button type="button" class="btn btn-secondary" id="add-besoin-btn" style="margin-bottom: 20px;">+ Ajouter un autre besoin</button>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/besoins" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.form-row {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.form-row .form-group {
    flex: 1;
    margin: 0;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.form-actions {
    margin-top: 30px;
}
</style>

<script nonce="<?= Flight::app()->get('csp_nonce') ?>">
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('besoins-container');
    const addBtn = document.getElementById('add-besoin-btn');

    function updateRemoveButtons() {
        const items = container.querySelectorAll('.besoin-item');
        const removeButtons = container.querySelectorAll('.remove-besoin');
        
        if (items.length > 1) {
            removeButtons.forEach(btn => btn.style.display = 'inline-block');
        } else {
            removeButtons.forEach(btn => btn.style.display = 'none');
        }
    }

    addBtn.addEventListener('click', function() {
        const firstItem = container.querySelector('.besoin-item');
        const newItem = firstItem.cloneNode(true);
        
        // Réinitialiser les valeurs
        newItem.querySelector('select').value = '';
        newItem.querySelector('input').value = '';
        
        // Ajouter l'event listener au bouton supprimer
        newItem.querySelector('.remove-besoin').addEventListener('click', function() {
            newItem.remove();
            updateRemoveButtons();
        });

        container.appendChild(newItem);
        updateRemoveButtons();
    });

    // Initialiser les boutons supprimer existants
    container.querySelectorAll('.remove-besoin').forEach(btn => {
        btn.addEventListener('click', function() {
            btn.closest('.besoin-item').remove();
            updateRemoveButtons();
        });
    });

    updateRemoveButtons();
});
</script>

<?php include __DIR__ . '/../include/footer.php'; ?>
