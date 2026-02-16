<?php include __DIR__ . '/include/header.php'; ?>

<div class="page-header">
    <div class="page-header-left">
        <div class="page-header-icon"><i class="bi bi-send"></i></div>
        <div class="page-header-text">
            <h1>Nouvelle distribution</h1>
            <p>Parcours de saisie en 3 étapes</p>
        </div>
    </div>
    <div class="page-header-actions">
        <a href="/distributions" class="btn btn-outline">
            <i class="bi bi-chevron-left"></i> Retour à la liste
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">

        <div class="stepper">
            <div class="stepper-step active">
                <div class="step-circle">1</div>
                <span class="step-label">Informations</span>
            </div>
            <div class="stepper-line"></div>
            <div class="stepper-step">
                <div class="step-circle">2</div>
                <span class="step-label">Détails</span>
            </div>
            <div class="stepper-line"></div>
            <div class="stepper-step">
                <div class="step-circle">3</div>
                <span class="step-label">Confirmation</span>
            </div>
        </div>

        <div class="stepper-content active">
            <div class="form-container">
                <h3 class="mb-2">Informations générales</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ville de distribution <span class="required">*</span></label>
                        <select class="form-select">
                            <option value="">— Sélectionner —</option>
                            <option>Antananarivo</option>
                            <option>Mahajanga</option>
                            <option>Toamasina</option>
                            <option>Fianarantsoa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date prévue <span class="required">*</span></label>
                        <input type="date" class="form-input" value="2026-02-20">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Responsable de la distribution</label>
                    <input type="text" class="form-input" placeholder="Nom du responsable">
                </div>
                <div class="form-group">
                    <label class="form-label">Site de distribution</label>
                    <input type="text" class="form-input" placeholder="Ex: Centre communal, École…">
                </div>
            </div>
        </div>

        <div class="stepper-content">
            <div class="form-container">
                <h3 class="mb-2">Détails de la distribution</h3>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Besoin <span class="required">*</span></label>
                        <select class="form-select">
                            <option value="">— Sélectionner —</option>
                            <option>Eau potable</option>
                            <option>Nourriture</option>
                            <option>Couvertures</option>
                            <option>Médicaments</option>
                            <option>Tentes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Quantité à distribuer <span class="required">*</span></label>
                        <input type="number" class="form-input" placeholder="Ex: 50" min="1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Nombre de bénéficiaires estimé</label>
                    <input type="number" class="form-input" placeholder="Ex: 120" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea class="form-textarea" rows="3" placeholder="Conditions particulières, remarques…"></textarea>
                </div>
            </div>
        </div>

        <div class="stepper-content">
            <div class="form-container text-center">
                <div style="margin: 24px 0;">
                    <div class="stat-icon green" style="width:64px;height:64px;margin:0 auto 16px;border-radius:50%;">
                        <i class="bi bi-check-lg" style="font-size: 32px;"></i>
                    </div>
                    <h3>Vérification avant envoi</h3>
                    <p class="text-muted mt-1">Relisez les informations saisies avant de confirmer.</p>
                </div>
                <div class="card" style="text-align:left;margin-top:24px;">
                    <div class="card-body">
                        <table class="data-table">
                            <tbody>
                                <tr><td><strong>Ville</strong></td><td>Antananarivo</td></tr>
                                <tr><td><strong>Date prévue</strong></td><td>20/02/2026</td></tr>
                                <tr><td><strong>Besoin</strong></td><td>Couvertures</td></tr>
                                <tr><td><strong>Quantité</strong></td><td>50 unités</td></tr>
                                <tr><td><strong>Bénéficiaires</strong></td><td>~120 familles</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions" style="justify-content: space-between;">
            <button class="btn btn-outline" id="stepper-prev" disabled>
                <i class="bi bi-chevron-left"></i> Précédent
            </button>
            <button class="btn btn-primary" id="stepper-next">
                Suivant <i class="bi bi-chevron-right"></i>
            </button>
        </div>

    </div>
</div>

<?php include __DIR__ . '/include/footer.php'; ?>
