<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-box-seam"></i></div>
        <div class="page-header-text">
            <h1>Nouvelle collecte</h1>
            <p>Enregistrer une nouvelle collecte de don</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="/collectes" class="btn btn-outline">
            <i class="bi bi-chevron-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="form-container">
            <form action="#" method="POST" onsubmit="return false;">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ville <span class="required">*</span></label>
                        <select class="form-select" required>
                            <option value="">— Sélectionner une ville —</option>
                            <option>Antananarivo</option>
                            <option>Mahajanga</option>
                            <option>Toamasina</option>
                            <option>Fianarantsoa</option>
                            <option>Antsirabe</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date de collecte <span class="required">*</span></label>
                        <input type="date" class="form-input" value="2026-02-16" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Type de besoin <span class="required">*</span></label>
                        <select class="form-select" required>
                            <option value="">— Sélectionner —</option>
                            <option>Nature</option>
                            <option>Matériel</option>
                            <option>Argent</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Besoin <span class="required">*</span></label>
                        <select class="form-select" required>
                            <option value="">— Sélectionner —</option>
                            <option>Eau potable</option>
                            <option>Nourriture</option>
                            <option>Couvertures</option>
                            <option>Médicaments</option>
                            <option>Tentes</option>
                            <option>Vêtements</option>
                            <option>Kits hygiène</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantité <span class="required">*</span></label>
                    <input type="number" class="form-input" placeholder="Ex: 100" min="1" required>
                    <div class="form-hint">Unité définie par le besoin sélectionné.</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Donateur (optionnel)</label>
                    <input type="text" class="form-input" placeholder="Nom de l'organisme ou du donateur">
                </div>

                <div class="form-group">
                    <label class="form-label">Observations</label>
                    <textarea class="form-textarea" placeholder="Notes complémentaires…" rows="4"></textarea>
                </div>

                <div class="form-actions">
                    <a href="/collectes" class="btn btn-outline">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Enregistrer
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
